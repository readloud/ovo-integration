import { getWalletTransaction } from './lib/ovo-api.mjs';

export default async (req) => {
  const token = req.headers.get('Authorization');
  if (!token) {
    return Response.json({ error: 'Authorization header required' }, { status: 401 });
  }
  try {
    const { searchParams } = new URL(req.url);
    const page = parseInt(searchParams.get('page') || '1', 10);
    const limit = parseInt(searchParams.get('limit') || '10', 10);
    const result = await getWalletTransaction(token, page, limit);
    return Response.json({ ok: true, data: result });
  } catch (err) {
    return Response.json({ error: err.message }, { status: err.status || 500 });
  }
};

export const config = {
  path: '/api/ovo/transactions',
};
