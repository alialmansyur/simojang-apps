<!-- Footer Dokumen & Penomoran Halaman -->
<htmlpagefooter name="docFooter">
    <table style="width: 100%; border-top: 0.5px solid #cbd5e1; font-size: 8pt; color: #64748b; padding-top: 4px;">
        <tr>
            <td style="text-align: left; width: 60%;">
                SIMOJANG PNBP &bull; Dokumen Resmi Kantor Regional III BKN &bull; Dicetak: <?= date('d/m/Y H:i') ?>
            </td>
            <td style="text-align: right; width: 40%;">
                Halaman {PAGENO} dari {nbpg}
            </td>
        </tr>
    </table>
</htmlpagefooter>
<sethtmlpagefooter name="docFooter" value="on" />
