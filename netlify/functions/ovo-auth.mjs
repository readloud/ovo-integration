import { loginWithPin } from './lib/ovo-api.mjs';

export default async (req) => {
  if (req.method !== 'POST') {
    return Response.json({ error: 'Method not allowed' }, { status: 405 });
  }
  try {
    const { pin, otpToken, phone, otpRefId, deviceId } = await req.json();
    if (!pin || !otpToken || !phone || !otpRefId || !deviceId) {
      return Response.json(
        { error: 'pin, otpToken, phone, otpRefId, and deviceId are required' },
        { status: 400 },
      );
    }
    const auth = await loginWithPin(pin, otpToken, phone, otpRefId, deviceId);
    return Response.json({ ok: true, auth });
  } catch (err) {
    return Response.json(
      { error: err.message, details: err.data },
      { status: err.status || 500 },
    );
  }
};

export const config = {
  path: '/api/ovo/auth',
};
