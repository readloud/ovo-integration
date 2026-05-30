import { transferOvo } from './lib/ovo-api.mjs';

export default async (req) => {
  if (req.method !== 'POST') {
    return Response.json({ error: 'Method not allowed' }, { status: 405 });
  }
  const token = req.headers.get('Authorization');
  if (!token) {
    return Response.json({ error: 'Authorization header required' }, { status: 401 });
  }
  try {
    const { to, amount, message } = await req.json();
    if (!to || !amount) {
      return Response.json({ error: 'to and amount are required' }, { status: 400 });
    }
    const result = await transferOvo(token, to, Number(amount), message);
    return Response.json({ ok: true, data: result });
  } catch (err) {
    return Response.json(
      { error: err.message, details: err.data },
      { status: err.status || 500 },
    );
  }
};

export const config = {
  path: '/api/ovo/transfer',
};
