import { publicEncrypt, constants } from 'node:crypto';

const BASE = 'https://api.ovo.id/';
const AUTH = 'https://agw.ovo.id/';
const AWS = 'https://apigw01.aws.ovo.id/';

const BASE_HEADERS = {
  'App-Version': '3.32.1',
  'User-Agent': 'OVO/17256 CFNetwork/1240.0.4 Darwin/20.5.0',
  OS: 'Android',
  'OS-Version': '7.1.1',
  'client-id': 'ovo_android',
};

async function ovoFetch(base, path, { method = 'GET', body, extra = {}, params } = {}) {
  const url = new URL(path, base);
  if (params) {
    for (const [k, v] of Object.entries(params)) {
      if (v !== null && v !== undefined) url.searchParams.set(k, String(v));
    }
  }

  const headers = { ...BASE_HEADERS, ...extra };
  const init = { method, headers };

  if (body !== undefined) {
    init.body = JSON.stringify(body);
    headers['Content-Type'] = 'application/json';
  }

  const res = await fetch(url.toString(), init);
  const json = await res.json().catch(() => ({}));

  if (!res.ok) {
    const msg =
      json?.message ||
      json?.error?.message ||
      json?.data?.message ||
      `OVO API error ${res.status}`;
    const err = new Error(msg);
    err.status = res.status;
    err.data = json;
    throw err;
  }

  return json;
}

function authExtra(token) {
  return { Authorization: token };
}

// ── Auth flow ─────────────────────────────────────────────────────────────────

export async function login2FA(phone, deviceId) {
  const resp = await ovoFetch(AUTH, 'v3/user/accounts/otp', {
    method: 'POST',
    body: {
      channel_code: 'ovo_android',
      device_id: deviceId,
      msisdn: phone,
      otp: { locale: 'ID', sms_hash: 'abc' },
    },
  });
  return {
    otp_ref_id: resp?.data?.otp?.otp_ref_id,
    device_id: deviceId,
  };
}

export async function login2FAVerify(refId, otp, phone, deviceId) {
  const resp = await ovoFetch(AUTH, 'v3/user/accounts/otp/validation', {
    method: 'POST',
    body: {
      channel_code: 'ovo_android',
      device_id: deviceId,
      msisdn: phone,
      otp: { otp, otp_ref_id: refId, type: 'LOGIN' },
    },
  });
  return resp?.data?.otp ?? resp;
}

async function fetchPublicKey() {
  const resp = await ovoFetch(AUTH, 'v3/user/public_keys', {});
  return resp?.data?.keys?.[0]?.key;
}

export async function loginWithPin(pin, otpToken, phone, otpRefId, deviceId) {
  const rsaKey = await fetchPublicKey();
  const ts = Date.now();
  const plain = `LOGIN|${pin}|${ts}|${deviceId}|${phone}|${deviceId}|${otpRefId}`;
  const encrypted = publicEncrypt(
    { key: rsaKey, padding: constants.RSA_PKCS1_PADDING },
    Buffer.from(plain, 'utf8'),
  ).toString('base64');

  const resp = await ovoFetch(AUTH, 'v3/user/accounts/login', {
    method: 'POST',
    body: {
      channel_code: 'ovo_android',
      credentials: {
        otp_token: otpToken,
        password: { format: 'rsa', value: encrypted },
      },
      device_id: deviceId,
      msisdn: phone,
      push_notification_id: 'XXXXXXXXXX',
    },
  });
  return resp?.data?.auth ?? resp;
}

// ── Authenticated endpoints ───────────────────────────────────────────────────

export function getProfile(token) {
  return ovoFetch(BASE, 'v3.0/api/front/', { extra: authExtra(token) });
}

export function getBalance(token) {
  return ovoFetch(BASE, 'wallet/inquiry', { extra: authExtra(token) });
}

export function getBudget(token) {
  return ovoFetch(BASE, 'v1.0/budget/detail', { extra: authExtra(token) });
}

export function getWalletTransaction(token, page = 1, limit = 10) {
  return ovoFetch(BASE, 'wallet/v2/transaction', {
    extra: authExtra(token),
    params: { page, limit, productType: '001' },
  });
}

export function getUnreadCount(token) {
  return ovoFetch(BASE, 'v1.0/notification/status/count/UNREAD', {
    extra: authExtra(token),
  });
}

export function getAllNotifications(token) {
  return ovoFetch(BASE, 'v1.0/notification/status/all', { extra: authExtra(token) });
}

export function isOVO(token, amount, mobilePhone) {
  return ovoFetch(BASE, 'v1.1/api/auth/customer/isOVO', {
    method: 'POST',
    extra: authExtra(token),
    body: { totalAmount: amount, mobile: mobilePhone },
  });
}

async function generateTrxId(token, amount, actionMark) {
  const resp = await ovoFetch(BASE, 'v1.0/api/auth/customer/genTrxId', {
    method: 'POST',
    extra: authExtra(token),
    body: { actionMark, amount },
  });
  return resp?.trxId ?? resp?.data?.trxId;
}

export async function transferOvo(token, toPhone, amount, message = '') {
  if (amount < 10000) throw new Error('Minimum transfer amount is Rp 10.000');
  const trxId = await generateTrxId(token, amount, 'trf_ovo');
  return ovoFetch(BASE, 'v1.0/api/customers/transfer', {
    method: 'POST',
    extra: authExtra(token),
    body: {
      amount,
      message: message || 'Sent via OVO API Wrapper',
      to: toPhone,
      trxId,
    },
  });
}

export function getRefBank(token) {
  return ovoFetch(BASE, 'v1.0/reference/master/ref_bank', { extra: authExtra(token) });
}

export function getBillers(token) {
  return ovoFetch(AWS, 'gpdm/ovo/ID/v2/billpay/get-billers', {
    extra: authExtra(token),
    params: { categoryID: '5C6' },
  });
}

export function logout(token) {
  return ovoFetch(BASE, 'v1.0/api/auth/customer/logout', { extra: authExtra(token) });
}
