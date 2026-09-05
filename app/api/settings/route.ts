import { NextResponse } from 'next/server'
import { query } from '@/lib/db'

export async function GET() {
  const result = await query('SELECT key, value FROM site_settings')
  return NextResponse.json({ settings: Object.fromEntries(result.rows.map((row) => [row.key, row.value])) }, { headers: { 'Cache-Control': 'no-store' } })
}
