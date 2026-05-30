import { getProfile, getBalance } from './lib/ovo-api.mjs';

export default async (req) => {
  const token = req.headers.get('Authorization');
  if (!token) {
    return Response.json({ error: 'Authorization header required' }, { status: 401 });
  }
  try {
    const [profileResp, balanceResp] = await Promise.all([
      getProfile(token).catch(() => null),
      getBalance(token).catch(() => null),
    ]);
    return Response.json({
      ok: true,
      profile: profileResp?.profile ?? profileResp,
      balance: balanceResp?.data ?? balanceResp,
    });
  } catch (err) {
    return Response.json({ error: err.message }, { status: err.status || 500 });
  }
};

export const config = {
  path: '/api/ovo/me',
};
