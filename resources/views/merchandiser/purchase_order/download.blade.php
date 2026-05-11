<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order</title>
    
    <style>
        
        body {
            margin: 0px;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 0.85rem;
            
            
            padding-top: 290px;
            padding-bottom: 70px;
        }

        
        .header {
            position: fixed;
            top: 0px;
            left: 0px;
            right: 0px;
            background-color: #ffffff;
            padding: 1.5rem;
            height: 160px; 
        }
        
        
        
        .content {
            padding: 0 1.5rem;
        }

        
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            background-color: #ffffff;
            text-align: center;
            padding: 1rem 1.5rem 2rem 1.5rem;
            height: 40px;
        }

        
        .pagenum:before {
            content: counter(page);
        }
        
        
        
        tfoot {
            display: table-footer-group;
        }

        .item-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>
<body style="background-color: #ffffff;">

    
    <div class="header">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
            <tr>
                
                <td width="60%" valign="top">
                    <img src="data:image/png;base64,{{ imageToBase64(__DIR__ . '/../../../public/image/agrinesia-logo1.png')}}" alt="Agrinesia Logo" style="height: 70px; margin-bottom: 1rem;">
                    
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                        <tr>
                            <td width="50%" valign="top" style="border: 1px solid black; border-right: none;">
                                <div style="text-align:center;background-color: #797148; color: white; font-weight: bold; padding: 0.5rem; font-size: 14px;">Vendor</div>
                                <div style="padding: 0.5rem; font-size: 13px; height: 60px;">
                                    @if ($vendor)
                                        <p style="font-weight: bold; margin: 0;">{{ $vendor->name }}</p>
                                    @endif
                                </div>
                            </td>
                            <td width="50%" valign="top" style="border: 1px solid black;">
                                <div style="text-align:center;background-color: #797148; color: white; font-weight: bold; padding: 0.5rem; font-size: 14px;">Tempat Pengiriman</div>
                                <div style="padding: 0.5rem; font-size: 13px; height: 60px;">
                                    <p style="font-weight: bold; margin: 0;">{{ $store->name }}</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:10%">

                </td>
                
                <td width="30%" valign="top">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="left" style="padding-bottom: 0.5rem;">
                                <h1 style="font-size: 1rem; font-weight: bold; border-bottom: 4px solid black; padding-bottom: 0.25rem; margin: 0; display: inline-block;width:100%">PURCHASE ORDER</h1>
                            </td>
                        </tr>
                        <tr>
                            <td align="left">
                                
                                <table width="100%" border="0" cellspacing="0" cellpadding="2" style="font-size: 0.85rem;">
                                    <tr>
                                        <td style="font-weight: bold;">No. PO</td>
                                        <td>:</td>
                                        <td>{{ $po->U_SOL_SYNC_KEY }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">NPWP</td>
                                        <td>:</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">Tanggal</td>
                                        <td>:</td>
                                        <td>{{ dateformatsimple($po->DocDate) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">No. PR</td>
                                        <td>:</td>
                                        <td>{{ $pr->U_SOL_SYNC_KEY }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">No. Vendor</td>
                                        <td>:</td>
                                        <td>{{ $vendor ? $vendor->code : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">VAT. Vendor</td>
                                        <td>:</td>
                                        <td>{{ $vendor ? $vendor->vatcode : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">Mata Uang</td>
                                        <td>:</td>
                                        <td>IDR</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">Tanggal Kirim</td>
                                        <td>:</td>
                                        <td>{{ date('d-m-Y', strtotime($po->DocDate)) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">Jatuh Tempo</td>
                                        <td>:</td>
                                        <td>-</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold;">Tipe PO</td>
                                        <td>:</td>
                                        <td></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    
    
    <div class="footer">
        <hr style="border: none; border-top: 1px solid #ccc; margin-bottom: 1rem;">
        <small>
            Document ini sah dan di proses oleh sistem. Silakan hubungi administrator apabila membutuhkan bantuan. Halaman&nbsp;&nbsp; <span class="pagenum"></span>
        </small>
    </div>

    <div class="content">
        
        <p style="font-weight:bold; margin-bottom: 0.5rem;">Mohon mengirimkan barang dibawah ini :</p>
        <table width="100%" style="border-collapse: collapse; font-size: 0.85rem; page-break-inside: auto;" class="item-table">
            <thead style="background-color: #797148; color: white;">
                <tr>
                    <th style="width: 40px; border: 1px solid black; padding: 0.5rem; text-align: center; font-weight: bold;">No.</th>
                    <th style="border: 1px solid black; padding: 0.5rem; text-align: center; font-weight: bold;">Deskripsi Barang</th>
                    <th style="width: 70px; border: 1px solid black; padding: 0.5rem; text-align: center; font-weight: bold;">Quantity</th>
                    <th style="width: 70px; border: 1px solid black; padding: 0.5rem; text-align: center; font-weight: bold;">Satuan</th>
                    <th style="width: 150px; border: 1px solid black; padding: 0.5rem; text-align: center; font-weight: bold;">Harga</th>
                    <th style="width: 150px; border: 1px solid black; padding: 0.5rem; text-align: center; font-weight: bold;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->items as $k => $v)
                <tr style="page-break-inside: avoid;">
                    <td style="height: 56px;border: 1px solid black; padding: 0.5rem; vertical-align:top; text-align: center;">{{ ++$k }}</td>
                    <td style="height: 56px;border: 1px solid black; padding: 0.5rem; vertical-align:top;">{{ $v->ItemCode }} - {{ $v->item->name }}</td>
                    <td style="height: 56px;border: 1px solid black; padding: 0.5rem; vertical-align:top; text-align: center;">{{ nominal($v->Quantity) }}</td>
                    <td style="height: 56px;border: 1px solid black; padding: 0.5rem; vertical-align:top; text-align: center;">{{ $v->item->unit_of_measurement }}</td>
                    <td style="height: 56px;border: 1px solid black; padding: 0.5rem; vertical-align:top; text-align: right;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="text-align: left;">IDR</td><td style="text-align: right;">{{ nominal($v->PriceBefDi) }}</td></tr></table>
                    </td>
                    <td style="height: 56px;border: 1px solid black; padding: 0.5rem; vertical-align:top; text-align: right;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="text-align: left;">IDR</td><td style="text-align: right;">{{ nominal($v->PriceBefDi * $v->Quantity) }}</td></tr></table>
                    </td>
                </tr>
                @endforeach
                @php
                    $itemPerPage = 10;
                    $totalItems = $po->items->count();
                    $lastPageItems = $totalItems % $itemPerPage;
                    $emptyRows = $itemPerPage - ($lastPageItems > 0 ? $lastPageItems : $itemPerPage);
                    $rowHeight = 58; // Height of each row in pixels
                    $totalHeight = $emptyRows * $rowHeight; // Total height for empty rows
                @endphp
                    <tr>
                        <td style="padding: 5px; border-bottom:1px solid black; border-left: 1px solid black; border-right: 1px solid black; height: {{$totalHeight}}px">&nbsp;</td>
                        <td style="padding: 5px; border-bottom:1px solid black; border-left: 1px solid black; border-right: 1px solid black; height: {{$totalHeight}}px">&nbsp;</td>
                        <td style="padding: 5px; border-bottom:1px solid black; border-left: 1px solid black; border-right: 1px solid black; height: {{$totalHeight}}px">&nbsp;</td>
                        <td style="padding: 5px; border-bottom:1px solid black; border-left: 1px solid black; border-right: 1px solid black; height: {{$totalHeight}}px">&nbsp;</td>
                        <td style="padding: 5px; border-bottom:1px solid black; border-left: 1px solid black; border-right: 1px solid black; height: {{$totalHeight}}px">&nbsp;</td>
                        <td style="padding: 5px; border-bottom:1px solid black; border-left: 1px solid black; border-right: 1px solid black; height: {{$totalHeight}}px">&nbsp;</td>
                    </tr>
            </tbody>
            <tfoot>
                <tr style="page-break-inside: avoid;">
                    <td colspan="4" valign="top">
                        <div style="margin-bottom: 1rem; margin-top:1rem;"><span style="font-weight: bold;">Cara Pembayaran :</span></div>
                         <div style="margin-bottom: 1rem;">
                            <span style="font-weight: bold;">Keterangan :</span>
                            <div>
                                {{ $po->U_SOL_SYNC_KEY }} {{ $po->Comments }}
                                <br>
                                Based On Purchase Request {{ $pr->U_SOL_SYNC_KEY }}.
                            </div>
                         </div>
                         <p style="font-weight: bold; margin: 0 0 0.2rem 0;">* No. PO harus dicantumkan pada saat Surat Jalan dan Invoice</p>
                         <p style="font-weight: bold; margin: 0 0 1rem 0;">Jangka waktu konfirmasi email PO adalah 48 jam setelah PO terkirim</p>
                    </td>
                    <td colspan="2" valign="top" style="padding: 0; border:1px solid black;">
                        <table width="100%" style="font-size: 0.85rem; border-collapse: collapse;">
                            <tr>
                                <td style="border-bottom: 1px solid black; padding: 0.5rem; font-weight: bold;" width="50%">Subtotal</td>
                                <td style="border-bottom: 1px solid black; border-left: 1px solid black; padding: 0.5rem;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="text-align: left;">IDR</td><td style="text-align: right;">{{ nominal($po->DocTotal - $po->total_pajak) }}</td></tr></table>
                                </td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid black; padding: 0.5rem; font-weight: bold;">Diskon</td>
                                <td style="border-bottom: 1px solid black; border-left: 1px solid black; padding: 0.5rem; text-align: right;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="text-align: left;">IDR</td><td style="text-align: right;">0</td></tr></table>
                                </td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid black; padding: 0.5rem; font-weight: bold;">PPN</td>
                                <td style="border-bottom: 1px solid black; border-left: 1px solid black; padding: 0.5rem; text-align: right;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="text-align: left;">IDR</td><td style="text-align: right;">{{ nominal($po->total_pajak) }}</td></tr></table>
                                </td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid black; padding: 0.5rem; font-weight: bold;">Biaya Kirim</td>
                                <td style="border-bottom: 1px solid black; border-left: 1px solid black; padding: 0.5rem; text-align: right;"></td>
                            </tr>
                            <tr style="background-color: #797148; color: white; font-weight: bold;">
                                <td style="padding: 0.5rem;">Total</td>
                                <td style="border-left: 1px solid black; padding: 0.5rem;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td style="text-align: left;">IDR</td><td style="text-align: right;">{{ nominal($po->DocTotal) }}</td></tr></table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="page-break-inside: avoid;">
                    <td colspan="6" style="padding-top: 1.5rem;">
                        <div style="font-size: 0.85rem;">
                             <div style="margin-bottom: 1rem;">
                                 <p style="font-weight: bold; margin: 0;">Invoicing :</p>
                                 <p style="font-weight: bold;margin: 0.25rem 0;">Invoice dapat dikirimkan ke:</p>
                                 <p style="font-weight: bold; margin: 0.25rem 0;">PT. Agrinesia Raya (Head Office - Bogor) Jl. Cahaya Raya Kav.L (Kawasan Industri Sentul), Leuwinutug, Citereup, Kab. Bogor, Jawa Barat 16813, Up. Finance - AP (Dea Safitri)</p>
                                 <p style="font-weight: bold;margin: 0.25rem 0;">Email: finance.ap@agrinesia.co.id - (Dea Safitri)</p>
                             </div>
                             
                             <div style="margin-bottom: 1rem;">
                                  <p style="font-weight: bold; margin: 0 0 0.5rem 0;">Kelengkapan Dokumen Invoice:</p>
                                  <ol style="margin: 0; padding-left: 1.5rem; list-style-position: outside;">
                                      <li style="margin-bottom: 1rem; padding-left: 0.25rem;"><strong>Invoice</strong><br>Invoice yang ditandatangani dan menggunakan stempel Perusahaan. Untuk penggunaan materai, silahkankan mengacu pada poin dibawah ini:
                                          <ul style="list-style-type: lower-alpha; padding-left: 1.25rem; margin-top: 0.5rem; list-style-position: outside;">
                                              <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;"><span style="font-weight: bold;">Materai Tempel:</span><br> Transaksi di atas Rp 5 Juta, wajib menggunakan meterai dan meterai WAJIB terkena tandatangan dan stempel (Berlaku untuk meterai tempel).</li>
                                              <li style="padding-left: 0.25rem;"><span style="font-weight: bold;">e-Materai:</span><br> Transaksi di atas Rp 5 Juta, wajib menggunakan meterai, meterai tidak boleh terkena tandan tangan dan tandatangan berada disebelah kanan meterai. Berlaku untuk e-Meterai).</li>
                                          </ul>
                                      </li>
                                      <li style="font-weight: bold;margin-bottom: 1rem; padding-left: 0.25rem;">Faktur Pajak (untuk transaksi dengan PPN) untuk Perusahaan PKP.</li>
                                      <li style="font-weight: bold;margin-bottom: 1rem; padding-left: 0.25rem;">Purchase Order yang diterbitkan oleh PT Agrinesia Raya.</li>
                                      <li style="padding-left: 0.25rem;">
                                        <span style="font-weight: bold;">Tanda terima, yang ditandatangani oleh PIC Agrinesia dan dilengkapi dengan nama jelas, jabatan dan stempel Agrinesia, dengan dokumen sebagai berikut:</span>
                                          <ul style="list-style-type: lower-alpha; padding-left: 1.25rem; margin-top: 0.5rem; list-style-position: outside;">
                                             <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;">Surat Jalan / Pengiriman Barang, atau</li>
                                             <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;">Berita Acara Serah Terima Pekerjaan.</li>
                                             <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;">Surat Keterangan Bebas Pajak (SKB), bila ada.</li>
                                             <li style="padding-left: 0.25rem;">Surat Keterangan Wajib Pajak memiliki Peredaran Bruto Tertentu (PP23), bila ada.</li>
                                          </ul>
                                      </li>
                                  </ol>
                             </div>
                             
                             <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td valign="top">
                                        <p style="font-weight: bold; margin: 0 0 0.5rem 0;">Pengiriman barang:</p>
                                        <ol style="padding-left: 1.5rem; margin: 0; list-style-position: outside;">
                                           <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;">Penerimaan barang hanya dilayani pada:<br> Hari: Senin - Jumat <br>Pukul: 07:00 - 15:00.<br> Diluar dari jadwal tersebut warehouse PT. Agrinesia Raya tidak dapat menerima pengiriman barang</li>
                                           <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;">Pihak PT. Agrinesia Raya tidak menyediakan jasa unloading, proses bongkar muat barang hingga ke warehouse PT. Agrinesia Raya adalah tanggung jawab Supplier.</li>
                                           <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;">Saat melakukan pengiriman barang, wajib melampirkan dokumen yg dibutuhkan (Surat Jalan yang tertera No. PO & copy PO)</li>
                                           <li style="padding-left: 0.25rem; margin-bottom: 0.5rem;">Dimohon dapat berpakaian rapi saat memasuki Kawasan Industri PT Agrinesia Raya.</li>
                                           <li style="padding-left: 0.25rem;">Untuk supplier yang menggunakan truck besar di mohon dapat menyediakan stopper ban</li>
                                        </ol>
                                    </td>
                                    
                                    <td width="220px" valign="top" align="center" style="padding-left: 1.5rem;">
                                         <div style="margin-bottom: 10px;">
                                            <p style="font-weight: bold; font-size:1.2rem; margin: 0;">SCAN DISINI</p>
                                            <p style="font-size: 0.85rem; margin: 5px 0 0 0;">Untuk pelaporan atas pelanggaran</p>
                                            <p style="font-size: 0.85rem; margin: 0;">atau masukan dari anda</p>
                                         </div>
                                        <div>
                                            @php
                                            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://form.jotform.com/Agrinesia/vendor';
                                            $qrCodeImage = file_get_contents($qrCodeUrl);
                                            $qrCodeBase64 = base64_encode($qrCodeImage);
                                            @endphp
                                             <img src="data:image/png;base64,{{$qrCodeBase64}}" alt="QR Code" style="width: 150px; height: 150px;">
                                        </div>
                                    </td>
                                </tr>
                             </table>
                        </div>
                    </td>
                </tr>
            </tfoot>
            </table>
    </div>

</body>
</html>