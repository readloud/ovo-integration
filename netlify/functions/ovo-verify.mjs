import { login2FAVerify } from './lib/ovo-api.mjs';

export default async (req) => {
  if (req.method !== 'POST') {
    return Response.json({ error: 'Method not allowed' }, { status: 405 });
  }
  try {
    const { refId, otp, phone, deviceId } = await req.json();
    if (!refId || !otp || !phone || !deviceId) {
      return Response.json(
        { error: 'refId, otp, phone, and deviceId are required' },
        { status: 400 },
      );
    }
    const result = await login2FAVerify(refId, otp, phone, deviceId);
    return Response.json({ ok: true, otp: result });
  } catch (err) {
    return Response.json(
      { error: err.message, details: err.data },
      { status: err.status || 500 },
    );
  }
};

export const config = {
  path: '/api/ovo/verify',
};
