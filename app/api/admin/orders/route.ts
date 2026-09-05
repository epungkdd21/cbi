import { NextRequest, NextResponse } from 'next/server'
import crypto from 'node:crypto'
import { query } from '@/lib/db'

function isAdmin(request: NextRequest) {
  const token = request.cookies.get('dwipantara_admin')?.value
  const expected = process.env.ADMIN_SESSION_TOKEN || crypto.createHash('sha256').update(process.env.ADMIN_PASSWORD || process.env.BETTER_AUTH_SECRET || '').digest('hex')
  return Boolean(token) && token === expected
}

export async function GET(request: NextRequest) {
  if (!isAdmin(request)) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const status = request.nextUrl.searchParams.get('status')
  const result = await query('SELECT id, provider_order_id, name, email, phone, quantity, amount, status, ticket_code, email_sent_at, whatsapp_sent_at, created_at, updated_at FROM ticket_orders WHERE ($1::text IS NULL OR status = $1) ORDER BY created_at DESC', [status || null])
  const totals = await query('SELECT COUNT(*)::int AS total, COUNT(*) FILTER (WHERE status = \'paid\')::int AS paid, COALESCE(SUM(amount) FILTER (WHERE status = \'paid\'), 0)::int AS revenue FROM ticket_orders')
  return NextResponse.json({ orders: result.rows, summary: totals.rows[0] })
}

export async function PATCH(request: NextRequest) {
  if (!isAdmin(request)) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const body = await request.json()
  const id = String(body.id || '')
  const status = String(body.status || '')
  if (!id || !['pending', 'paid', 'failed', 'cancelled'].includes(status)) return NextResponse.json({ error: 'Data status tidak valid.' }, { status: 400 })
  const result = await query('UPDATE ticket_orders SET status = $1, updated_at = now() WHERE id = $2 RETURNING id, status', [status, id])
  if (!result.rows[0]) return NextResponse.json({ error: 'Order tidak ditemukan.' }, { status: 404 })
  return NextResponse.json({ order: result.rows[0] })
}
