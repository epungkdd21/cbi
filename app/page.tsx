'use client'

import { useMemo, useState } from 'react'
import QRCode from 'qrcode'

const dates = [
  { day: 'JUM', date: '20', month: 'JUN' },
  { day: 'SAB', date: '21', month: 'JUN' },
  { day: 'MIN', date: '22', month: 'JUN' },
  { day: 'SEN', date: '23', month: 'JUN' },
]

const cinemas = ['CGV Grand Indonesia', 'Cinépolis Senayan Park', 'XXI Plaza Indonesia']
const showtimes = ['12:15', '14:40', '17:05', '19:30', '21:55']
const occupiedSeats = ['A3', 'A4', 'B7', 'C2', 'C3', 'D8', 'E5', 'F6', 'G1', 'G2']
const seatRows = ['A', 'B', 'C', 'D', 'E', 'F', 'G']
const seatNumbers = [1, 2, 3, 4, 5, 6, 7, 8]
const ticketPrice = 55000
const serviceFee = 3000
const formatRupiah = (value: number) => `Rp${value.toLocaleString('id-ID')}`

export default function Page() {
  const [selectedDate, setSelectedDate] = useState(1)
  const [selectedCinema, setSelectedCinema] = useState(cinemas[0])
  const [selectedTime, setSelectedTime] = useState(showtimes[2])
  const [selectedSeats, setSelectedSeats] = useState<string[]>(['D4'])
  const [step, setStep] = useState(1)
  const [buyer, setBuyer] = useState({ name: '', email: '', phone: '' })
  const payment = 'QRIS'
  const [message, setMessage] = useState('')
  const [qrImage, setQrImage] = useState<string | null>(null)
  const [transactionId, setTransactionId] = useState<string | null>(null)

  const total = useMemo(() => selectedSeats.length * ticketPrice + serviceFee, [selectedSeats.length])

  function toggleSeat(seat: string) {
    if (occupiedSeats.includes(seat)) return
    setSelectedSeats((current) => current.includes(seat) ? current.filter((item) => item !== seat) : current.length < 6 ? [...current, seat] : current)
    setMessage('')
  }

  function continueToBuyer() {
    if (!selectedSeats.length) {
      setMessage('Pilih minimal satu kursi untuk melanjutkan.')
      return
    }
    setStep(2)
    setMessage('')
  }

  async function submitPayment(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()
    if (!buyer.name || !buyer.email || !buyer.phone) {
      setMessage('Lengkapi data pemesan terlebih dahulu.')
      return
    }

    setMessage('Menghubungkan ke PayKita...')
    try {
      const response = await fetch('/api/paykita/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          buyer,
          paymentMethod: payment,
          cinema: selectedCinema,
          showtime: selectedTime,
          seats: selectedSeats,
          date: dates[selectedDate],
          amount: total,
        }),
      })
      const result = await response.json()
      if (!response.ok) throw new Error(result.error || 'Pembayaran belum tersedia.')
      if (result.paymentUrl) {
        window.location.href = result.paymentUrl
        return
      }
      const qrisPayload = typeof result.qris === 'string' ? result.qris : ''
      const providerImage = typeof result.qrImage === 'string' ? result.qrImage : ''
      const imageSource = providerImage || qrisPayload
      const isImageSource = imageSource.startsWith('data:image/') || imageSource.startsWith('http://') || imageSource.startsWith('https://')
      const generatedQrImage = imageSource
        ? isImageSource
          ? imageSource
          : providerImage
            ? `data:image/png;base64,${providerImage}`
            : await QRCode.toDataURL(qrisPayload, { width: 640, margin: 2, errorCorrectionLevel: 'M' })
        : null
      setQrImage(generatedQrImage)
      setTransactionId(result.transactionId || null)
      setStep(3)
      setMessage(generatedQrImage ? 'Scan QRIS berikut untuk menyelesaikan pembayaran.' : 'Pesanan berhasil dibuat, tetapi data QRIS belum dikirim oleh PayKita.')
    } catch (error) {
      setMessage(error instanceof Error ? error.message : 'Pembayaran gagal dibuat. Coba lagi.')
    }
  }

  return (
    <main className="min-h-screen bg-background text-foreground">
      <header className="border-b border-border/70 bg-background/90 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 lg:px-8">
          <div className="flex items-center gap-3">
            <div className="brand-mark">D</div>
            <div>
              <p className="font-mono text-[10px] font-bold uppercase tracking-[0.28em] text-primary">Dwipantara</p>
              <p className="text-xs text-muted-foreground">Tiket resmi pemutaran film</p>
            </div>
          </div>
          <div className="flex items-center gap-2 text-xs font-medium text-muted-foreground"><span className="secure-dot" /> Pembayaran aman</div>
        </div>
      </header>

      <section className="mx-auto grid max-w-6xl gap-8 px-5 pb-12 pt-8 lg:grid-cols-[1fr_340px] lg:px-8 lg:pt-12">
        <div>
          <div className="mb-8 flex items-end justify-between gap-4">
            <div>
              <p className="eyebrow">Pesan tiket sekarang</p>
              <h1 className="mt-2 max-w-2xl text-balance font-serif text-4xl font-medium leading-[1.05] tracking-tight sm:text-6xl">Satu layar. <em>Satu perjalanan.</em></h1>
              <p className="mt-4 max-w-xl text-sm leading-6 text-muted-foreground">Nikmati Dwipantara, kisah perjalanan lintas ruang dan waktu. Pilih jadwal dan kursimu di sini.</p>
            </div>
            <div className="hidden text-right sm:block"><p className="font-mono text-3xl font-semibold text-primary">01</p><p className="font-mono text-[10px] uppercase tracking-[0.22em] text-muted-foreground">03 langkah</p></div>
          </div>

          <nav aria-label="Progres pemesanan" className="mb-8 flex items-center gap-3 border-y border-border/70 py-4">
            {['Pilih tiket', 'Data pemesan', 'Pembayaran'].map((label, index) => <div key={label} className={`flex items-center gap-2 text-xs font-semibold ${step === index + 1 ? 'text-primary' : step > index + 1 ? 'text-foreground' : 'text-muted-foreground'}`}><span className={`step-number ${step > index + 1 ? 'step-done' : ''}`}>{step > index + 1 ? '✓' : index + 1}</span>{label}{index < 2 && <span className="ml-1 text-border">—</span>}</div>)}
          </nav>

          {step === 1 && <section aria-labelledby="schedule-heading">
            <div className="mb-6 flex items-center justify-between"><div><p className="eyebrow">01 / Jadwal</p><h2 id="schedule-heading" className="mt-1 text-2xl font-semibold">Kapan kamu ingin menonton?</h2></div><span className="badge">Jakarta</span></div>
            <div className="date-grid">{dates.map((item, index) => <button type="button" key={item.date} onClick={() => setSelectedDate(index)} className={`date-card ${selectedDate === index ? 'date-selected' : ''}`}><span>{item.day}</span><strong>{item.date}</strong><span>{item.month}</span></button>)}</div>
            <div className="mt-7 grid gap-6 md:grid-cols-2">
              <label className="field-label">Pilih bioskop<select value={selectedCinema} onChange={(event) => setSelectedCinema(event.target.value)} className="select-field">{cinemas.map((cinema) => <option key={cinema}>{cinema}</option>)}</select></label>
              <div><span className="field-label">Pilih jam tayang</span><div className="time-grid">{showtimes.map((time) => <button type="button" key={time} onClick={() => setSelectedTime(time)} className={`time-button ${selectedTime === time ? 'time-selected' : ''}`}>{time}</button>)}</div></div>
            </div>
            <div className="mt-10 border-t border-border/70 pt-7"><div className="mb-5 flex items-end justify-between"><div><p className="eyebrow">02 / Kursi</p><h2 className="mt-1 text-2xl font-semibold">Pilih tempat terbaikmu</h2></div><p className="text-xs text-muted-foreground">Maks. 6 kursi</p></div><div className="screen-line">LAYAR</div><div className="seat-map">{seatRows.map((row) => <div key={row} className="seat-row"><span className="row-label">{row}</span>{seatNumbers.map((number) => { const seat = `${row}${number}`; const isOccupied = occupiedSeats.includes(seat); const isSelected = selectedSeats.includes(seat); return <button type="button" aria-label={`Kursi ${seat}`} aria-pressed={isSelected} disabled={isOccupied} key={seat} onClick={() => toggleSeat(seat)} className={`seat ${isOccupied ? 'seat-occupied' : isSelected ? 'seat-selected' : ''}`}>{number}</button> })}</div>)}</div><div className="mt-5 flex flex-wrap gap-5 text-xs text-muted-foreground"><span><i className="legend-seat" /> Tersedia</span><span><i className="legend-seat legend-selected" /> Dipilih</span><span><i className="legend-seat legend-occupied" /> Terisi</span></div></div>
            <div className="mt-8 flex flex-col items-start justify-between gap-4 border-t border-border/70 pt-5 sm:flex-row sm:items-center"><p className="text-sm text-muted-foreground">{selectedSeats.length} kursi dipilih <strong className="text-foreground">{selectedSeats.join(', ')}</strong></p><button type="button" onClick={continueToBuyer} className="primary-button">Lanjutkan <span>→</span></button></div>
          </section>}

          {step === 2 && <form onSubmit={submitPayment} aria-labelledby="buyer-heading"><div className="mb-7 flex items-center justify-between"><div><p className="eyebrow">02 / Data pemesan</p><h2 id="buyer-heading" className="mt-1 text-2xl font-semibold">Ke mana tiket dikirim?</h2></div><button type="button" className="back-button" onClick={() => setStep(1)}>← Ubah kursi</button></div><div className="form-grid"><label className="field-label">Nama lengkap<input required value={buyer.name} onChange={(event) => setBuyer({ ...buyer, name: event.target.value })} className="input-field" placeholder="Nama sesuai identitas" /></label><label className="field-label">Email<input required type="email" value={buyer.email} onChange={(event) => setBuyer({ ...buyer, email: event.target.value })} className="input-field" placeholder="nama@email.com" /></label><label className="field-label">Nomor WhatsApp<input required type="tel" value={buyer.phone} onChange={(event) => setBuyer({ ...buyer, phone: event.target.value })} className="input-field" placeholder="08xxxxxxxxxx" /></label></div><div className="mt-9 border-t border-border/70 pt-7"><p className="eyebrow">03 / Pembayaran</p><h2 className="mt-1 text-2xl font-semibold">Pilih metode pembayaran</h2><div className="mt-5 grid max-w-sm gap-3"><div className="payment-option payment-selected" aria-label="Metode pembayaran QRIS aktif"><span className="payment-icon">▦</span><span>QRIS</span><span className="payment-check">✓</span></div></div><p className="mt-3 text-xs text-muted-foreground">Bayar cepat dengan memindai kode QRIS melalui aplikasi pembayaran pilihanmu.</p></div><div className="mt-8 flex flex-col justify-between gap-4 border-t border-border/70 pt-5 sm:flex-row sm:items-center"><button type="button" className="back-button" onClick={() => setStep(1)}>← Kembali</button><button type="submit" className="primary-button">Bayar dengan PayKita <span>→</span></button></div></form>}

          {step === 3 && <section className="success-panel" aria-labelledby="success-heading"><div className="success-icon">✓</div><p className="eyebrow">Menunggu pembayaran</p><h2 id="success-heading" className="mt-2 text-3xl font-semibold">Scan QRIS untuk membayar.</h2><p className="mt-3 max-w-md text-sm leading-6 text-muted-foreground">Buka aplikasi pembayaran pilihanmu, scan kode QRIS di bawah, lalu tunggu konfirmasi pembayaran.</p>{qrImage ? <div className="mt-6 flex max-w-sm flex-col items-center gap-4 rounded-xl border border-border bg-card p-5"><img src={qrImage} alt="QRIS pembayaran tiket Dwipantara" className="size-64 rounded-lg object-contain" /><p className="text-center text-xs text-muted-foreground">Jangan tutup halaman ini sampai pembayaran terverifikasi.</p></div> : <div className="mt-6 rounded-lg border border-border bg-card px-4 py-3 text-sm text-muted-foreground">PayKita belum mengirim gambar QRIS. Periksa respons API atau gunakan URL pembayaran yang diberikan.</div>}{transactionId && <p className="mt-4 font-mono text-xs text-muted-foreground">ID transaksi: {transactionId}</p>}<button type="button" className="back-button mt-7" onClick={() => { setStep(2); setQrImage(null); setTransactionId(null) }}>← Kembali</button></section>}
          {message && <p role="status" className="mt-5 rounded-lg border border-primary/30 bg-primary/5 px-4 py-3 text-sm text-primary">{message}</p>}
        </div>

        <aside className="order-summary"><div className="poster-art"><div className="poster-glow" /><p className="poster-kicker">Sebuah film karya</p><h2>DWIPANTARA</h2><p className="poster-subtitle">melampaui batas waktu</p><div className="poster-stamp">TIKET<br />RESMI</div></div><div className="summary-body"><div className="flex items-start justify-between gap-4"><div><p className="eyebrow">Ringkasan pesanan</p><h2 className="mt-1 text-xl font-semibold">Dwipantara</h2></div><span className="badge">2j 18m</span></div><div className="summary-info"><div><span>Tanggal</span><strong>{dates[selectedDate].day}, {dates[selectedDate].date} {dates[selectedDate].month} 2025</strong></div><div><span>Lokasi</span><strong>{selectedCinema}</strong></div><div><span>Jam tayang</span><strong>{selectedTime} WIB · Studio 04</strong></div><div><span>Kursi</span><strong>{selectedSeats.length ? selectedSeats.join(', ') : 'Belum dipilih'}</strong></div></div><div className="summary-total"><div><span>{selectedSeats.length} × Tiket Reguler</span><strong>{formatRupiah(selectedSeats.length * ticketPrice)}</strong></div><div><span>Biaya layanan</span><strong>{formatRupiah(serviceFee)}</strong></div><div className="total-line"><span>Total pembayaran</span><strong>{formatRupiah(total)}</strong></div></div><p className="summary-note">Tiket elektronik akan dikirim ke email setelah pembayaran berhasil.</p></div></aside>
      </section>
      <footer className="mx-auto flex max-w-6xl justify-between border-t border-border/70 px-5 py-6 text-xs text-muted-foreground lg:px-8"><span>© 2025 Dwipantara</span><span>Powered by PayKita</span></footer>
    </main>
  )
}
