let genderChart;
loadData();

async function loadData(keyword = '') {
    const response = await fetch(AppConfig.initGlobal + 'fetch/data-statistik-accum', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            unit: selectedData,
        }),
    });
    const data = await response.json();
    pageLoaded(data);
}

function pageLoaded(data) {
    res = data.list;
    const totalBup = res.total_menjelang_bup ?? 0;
    const totalTelahBup = res.total_telah_bup ?? 0;

    $('#bup-alert').html(`
        <div class="alert alert-warning rounded" style="border-radius:1.25em !important;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Terdapat <strong>${totalBup} Pegawai</strong> yang akan menjelang pensiun
        </div>
    `);

    $('#totalPegawai').text(res.total_pegawai || 0);
    $('#activeStat').text(
        (res.total_aktif || 0) + ' (' + (res.persen_aktif || 0) + '%)'
    );
    $('#inactiveStat').text(
        (res.total_nonaktif || 0) + ' (' + (res.persen_nonaktif || 0) + '%)'
    );
    $('#maleStat').text(
        res.total_pria + ' (' + res.persen_pria + '%)'
    );
    $('#femaleStat').text(
        res.total_wanita + ' (' + res.persen_wanita + '%)'
    );
    $('#boomerStat').text(res.baby_boomer);
    $('#genxStat').text(res.gen_x);
    $('#genyStat').text(res.gen_y);
    $('#genzStat').text(res.gen_z);
    $('#alphaStat').text(res.gen_alpha);

    // ================= UPDATE GENDER CHART =================
    genderChart.updateSeries([
        parseInt(res.total_pria),
        parseInt(res.total_wanita)
    ]);

}



// ================= GENDER DONUT =================
genderChart = new ApexCharts(document.querySelector("#genderChart"), {
    chart: {
        type: 'donut',
        height: 180
    },
    labels: ['Pria', 'Wanita'],
    series: [0, 0],
    legend: {
        position: 'bottom'
    },
    dataLabels: {
        enabled: false
    },
    colors: ['#1040c1', '#d63384']
});
genderChart.render();
