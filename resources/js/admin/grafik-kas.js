// Grafik pemasukan & pengeluaran per bulan (dashboard). Data dari atribut
// data-grafik-kas (JSON dari server). ECharts diimpor dinamis agar halaman lain
// tidak ikut memuat pustaka grafik yang besar.

const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});

// Sumbu Y diringkas (1,5 jt) agar label tidak memakan lebar grafik.
const ringkas = (nilai) => {
    const satuan = [
        [1e12, ' T'],
        [1e9, ' M'],
        [1e6, ' jt'],
        [1e3, ' rb'],
    ].find(([batas]) => Math.abs(nilai) >= batas);

    return satuan
        ? (nilai / satuan[0]).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + satuan[1]
        : nilai.toLocaleString('id-ID');
};

// Warna dibaca dari token CSS Gentelella agar ikut mode terang/gelap.
const warnaTema = () => {
    const css = getComputedStyle(document.documentElement);
    const ambil = (nama) => css.getPropertyValue(nama).trim();

    return {
        hijau: ambil('--green'),
        merah: ambil('--red'),
        teks: ambil('--text'),
        teksPudar: ambil('--text-muted'),
        garis: ambil('--border-color-light'),
        permukaan: ambil('--bg-surface'),
    };
};

const opsiGrafik = (data, w) => ({
    textStyle: { fontFamily: "'Inter', sans-serif", fontSize: 11, color: w.teksPudar },
    grid: { left: 8, right: 8, top: 36, bottom: 8, containLabel: true },
    legend: {
        top: 0,
        right: 0,
        icon: 'roundRect',
        itemWidth: 10,
        itemHeight: 10,
        textStyle: { color: w.teks },
    },
    tooltip: {
        trigger: 'axis',
        axisPointer: { type: 'shadow' },
        // Judul tooltip memakai nama bulan lengkap; label sumbu X sengaja disingkat.
        // Semua teks berasal dari server (nama bulan) atau tetap, bukan input pengguna.
        formatter: (titik) =>
            [
                data.labelPanjang[titik[0].dataIndex],
                ...titik.map((t) => `${t.marker} ${t.seriesName}: <b>${rupiah.format(t.value)}</b>`),
            ].join('<br>'),
        backgroundColor: w.permukaan,
        borderColor: w.garis,
        textStyle: { color: w.teks, fontSize: 12 },
    },
    xAxis: {
        type: 'category',
        data: data.label,
        axisLine: { lineStyle: { color: w.garis } },
        axisTick: { show: false },
        // interval 0 = tampilkan semua bulan; hideOverlap menyembunyikan sebagian di layar sempit.
        axisLabel: { color: w.teksPudar, interval: 0, hideOverlap: true, lineHeight: 14 },
    },
    yAxis: {
        type: 'value',
        splitLine: { lineStyle: { color: w.garis, type: [4, 3] } },
        axisLabel: { color: w.teksPudar, formatter: ringkas },
    },
    series: [
        { name: 'Pemasukan', data: data.pemasukan, color: w.hijau },
        { name: 'Pengeluaran', data: data.pengeluaran, color: w.merah },
    ].map((seri) => ({
        ...seri,
        type: 'bar',
        barMaxWidth: 16,
        itemStyle: { borderRadius: [3, 3, 0, 0] },
    })),
});

export async function pasangGrafikKas() {
    const el = document.querySelector('[data-grafik-kas]');
    if (!el) {
        return;
    }

    let data;
    try {
        data = JSON.parse(el.dataset.grafikKas);
    } catch {
        // JSON rusak tidak boleh menghentikan skrip halaman.
        return;
    }

    const { default: echarts } = await import('./echarts.js');

    el.classList.remove('skeleton', 'chart-skeleton');
    let grafik = echarts.init(el);
    grafik.setOption(opsiGrafik(data, warnaTema()));

    new ResizeObserver(() => grafik.resize()).observe(el);

    // Warna diambil sekali saat init, jadi grafik dibangun ulang ketika tema berganti.
    new MutationObserver(() => {
        grafik.dispose();
        grafik = echarts.init(el);
        grafik.setOption(opsiGrafik(data, warnaTema()));
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
}
