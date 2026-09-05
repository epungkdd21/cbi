import { NextResponse } from 'next/server'
import { query } from '@/lib/db'

export async function GET() {
  const result = await query('SELECT id, title, slug, description, "imagePath" FROM event_content WHERE published = true ORDER BY "createdAt" DESC')
  return NextResponse.json({ items: result.rows }, { headers: { 'Cache-Control': 'public, s-maxage=60, stale-while-revalidate=300' } })
}
