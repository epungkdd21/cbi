import { NextResponse } from 'next/server'
import { randomUUID } from 'node:crypto'
import { query } from '@/lib/db'

function pick(value: unknown, keys: string[]): string | undefined {
  if (!value || typeof value !== 'object') return undefined
  const record = value as Record<string, unknown>
  for (const key of keys) {
    const item = record[key]
    if (typeof item === 'string' || typeof item === 'number') return String(item)
  }
  for (const item of Object.values(record)) {
    const nested = pick(item, keys)
    if (nested) return nested
  }
  return undefined
}

function normalizePhone(phone: string) {
  const digits = phone.replace(/\D/g, '')
  return digits.startsWith('0') ? `62${digits.slice(1)}` : digits
}

async function sendEmail(order: Record<string, string | number>, ticketCode: string) {
  const key = process.env.RESEND_API_KEY
  if (!key) throw new Error('RESEND_API_KEY belum tersedia')
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(ticketCode)}`
  const response = await fetch('https://api.resend.com/emails', {
    method: 'POST',
    headers: { Authorization: `Bearer ${key}`, 'Content-Type': 'application/json', 'Idempotency-Key': `ticket-email/${order.id}` },
    body: JSON.stringify({
      from: process.env.RESEND_FROM_EMAIL || 'Dwipantara <onboarding@resend.dev>',
      to: [order.email],
      subject: `Tiket Dwipantara - ${ticketCode}`,
      html: `<h1>Tiket Dwipantara</h1><p>Halo ${order.name}, pembayaran kamu berhasil.</p><p><b>Kode tiket:</b> ${ticketCode}</p><p><b>Jumlah tiket:</b> ${order.quantity}</p><p><b>Total:</b> Rp${Number(order.amount).toLocaleString('id-ID')}</p><img src="${qrUrl}" alt="QR tiket ${ticketCode}" /><p>Tunjukkan QR ini saat masuk acara.</p>`,
    }),
  })
  if (!response.ok) throw new Error(`Resend HTTP ${response.status}`)
}

async function sendWhatsApp(order: Record<string, string | number>, ticketCode: string) {
  const token = process.env.FONNTE_TOKEN
  if (!token) throw new Error('FONNTE_TOKEN belum tersedia')
  const response = await fetch('https://api.fonnte.com/send', {
    method: 'POST',
    headers: { Authorization: token, 'Content-Type': 'application/json' },
    body: JSON.stringify({ target: normalizePhone(String(order.phone)), message: `Tiket Dwipantara berhasil dibayar.\nKode tiket: ${ticketCode}\nJumlah: ${order.quantity}\nTotal: Rp${Number(order.amount).toLocaleString('id-ID')}\nTunjukkan kode ini saat masuk acara.` }),
  })
  if (!response.ok) throw new Error(`Fonnte HTTP ${response.status}`)
}

export async function POST(request: Request) {
  try {
    const payload = await request.json()
    const providerOrderId = pick(payload, ['order_id', 'orderId', 'id', 'reference'])
    const status = (pick(payload, ['status', 'payment_status', 'paymentStatus']) || '').toLowerCase()
    const paid = ['paid', 'success', 'settlement', 'completed', 'capture'].includes(status)
    if (!providerOrderId) return NextResponse.json({ error: 'order_id wajib diisi' }, { status: 400 })
    const existing = await query<Record<string, string | number>>('SELECT * FROM ticket_orders WHERE provider_order_id = $1 LIMIT 1', [providerOrderId])
    if (!existing.rows[0]) return NextResponse.json({ ok: true, ignored: true })
    const order = existing.rows[0]
    await query('UPDATE ticket_orders SET status = $1, updated_at = now() WHERE provider_order_id = $2', [paid ? 'paid' : status || 'pending', providerOrderId])
    if (!paid || (order.email_sent_at && order.whatsapp_sent_at)) return NextResponse.json({ ok: true, status: paid ? 'paid' : status })
    const ticketCode = String(order.ticket_code || `DWP-${randomUUID().slice(0, 8).toUpperCase()}`)
    await query('UPDATE ticket_orders SET ticket_code = $1, updated_at = now() WHERE id = $2', [ticketCode, order.id])
    const results = await Promise.allSettled([sendEmail(order, ticketCode), sendWhatsApp(order, ticketCode)])
    if (results[0].status === 'fulfilled') await query('UPDATE ticket_orders SET email_sent_at = now() WHERE id = $1', [order.id])
    if (results[1].status === 'fulfilled') await query('UPDATE ticket_orders SET whatsapp_sent_at = now() WHERE id = $1', [order.id])
    return NextResponse.json({ ok: true, ticketCode, email: results[0].status, whatsapp: results[1].status })
  } catch (error) {
    console.error('[v0] PayKita webhook error:', error)
    return NextResponse.json({ error: 'Webhook gagal diproses' }, { status: 500 })
  }
}

export async function GET() { return NextResponse.json({ endpoint: 'PayKita webhook aktif' }) }
