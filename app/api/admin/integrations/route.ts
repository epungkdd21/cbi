import { NextRequest, NextResponse } from 'next/server'
import crypto from 'node:crypto'

function isAdmin(request: NextRequest) {
  const token = request.cookies.get('dwipantara_admin')?.value
  const expected = process.env.ADMIN_SESSION_TOKEN || crypto.createHash('sha256').update(process.env.ADMIN_PASSWORD || process.env.BETTER_AUTH_SECRET || '').digest('hex')
  return Boolean(token) && token === expected
}

function mask(value?: string) {
  if (!value) return { configured: false, preview: 'Belum dikonfigurasi' }
  if (value.length < 8) return { configured: true, preview: '••••••••' }
  return { configured: true, preview: `${value.slice(0, 4)}••••••••${value.slice(-4)}` }
}

export async function GET(request: NextRequest) {
  if (!isAdmin(request)) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  return NextResponse.json({
    integrations: {
      paykita: mask(process.env.PAYKITA_API_KEY),
      resend: mask(process.env.RESEND_API_KEY),
      fonnte: mask(process.env.FONNTE_TOKEN),
      blob: mask(process.env.BLOB_READ_WRITE_TOKEN),
    },
  })
}

export async function POST(request: NextRequest) {
  if (!isAdmin(request)) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const body = await request.json().catch(() => ({}))
  const service = typeof body.service === 'string' ? body.service : ''
  const configured = Boolean({ paykita: process.env.PAYKITA_API_KEY, resend: process.env.RESEND_API_KEY, fonnte: process.env.FONNTE_TOKEN, blob: process.env.BLOB_READ_WRITE_TOKEN }[service as 'paykita' | 'resend' | 'fonnte' | 'blob'])
  if (!configured) return NextResponse.json({ ok: false, error: 'Integrasi belum dikonfigurasi.' }, { status: 400 })
  return NextResponse.json({ ok: true, message: `${service} tersedia di environment project.` })
}
