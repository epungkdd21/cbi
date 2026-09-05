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
  const result = await query('SELECT key, value FROM site_settings ORDER BY key')
  return NextResponse.json({ settings: Object.fromEntries(result.rows.map((row) => [row.key, row.value])) })
}

export async function PUT(request: NextRequest) {
  if (!isAdmin(request)) return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
  const body = await request.json()
  const entries = Object.entries(body.settings || {}).filter(([key, value]) => /^[a-zA-Z0-9_.-]{1,80}$/.test(key) && typeof value === 'string')
  for (const [key, value] of entries) await query('INSERT INTO site_settings (key, value, updated_at) VALUES ($1, $2, now()) ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, updated_at = now()', [key, value])
  return NextResponse.json({ saved: entries.length })
}
