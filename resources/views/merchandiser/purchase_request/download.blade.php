<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM PURCHASE REQUEST</title>
    <style>
        /* --- General Styles --- */
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* --- Page Break Control --- */
        /* This is the key for making tables break across pages in DomPDF */
        .item-table {
            page-break-inside: auto;
        }

        .item-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        /* This ensures the header repeats on each new page */
        .item-table thead {
            display: table-header-group;
        }

        .item-table tfoot {
            display: table-footer-group;
        }

        /* --- Specific Element Styles --- */
        .header-item {
            background-color: rgb(0, 118, 85);
            color: #fff;
            font-weight: bold;
            padding: 8px;
            text-align: left;
        }

        .border {
            border-collapse: collapse;
            width: 100%;
        }

        .border td, .border th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .info-table td {
            border: none;
            padding: 2px 4px;
        }

        .logo-container td {
            border: none;
            padding: 0;
        }
        
        .logo-text b {
            display: block;
            margin-bottom: 5px;
        }

        .logo-text p {
            margin: 0;
            font-size: 10px;
        }

        .verificator {
            display: inline-block;
            width: 250px;
            height: 60px;
            text-align: center;
        }

        .footer-note {
            text-align: center;
        }

    </style>
</head>
<body>

    <h3 style="text-align:center">FORM PURCHASE REQUEST</h3>

    <table style="width:100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;padding:10px;text-align:left;align:left">
                <img src="data:image/png;base64,{{ imageToBase64(__DIR__ . '/../../../public/image/agrinesia-logo1.png')}}" alt="Agrinesia Logo" style="height: 40px;">
                
                <p style="padding-left: 10px;">
                    <b>PT. Agrinesia Raya</b>
                </p>
                <p style="padding-left: 10px;">Kawasan Industri Sentul, Jl. Cahaya Raya Blok L, Leuwinutug, Citeureup, Bogor</p>
                
                
            </td>
            <td style="width: 50%; vertical-align: top; border:solid 1px silver">
                <table class="info-table" style="height:112px">
                    <tr>
                        <td>PR No.</td>
                        <td>:</td>
                        <td>{{ $pr->U_SOL_SYNC_KEY }}</td>
                    </tr>
                    <tr>
                        <td>Request Date</td>
                        <td>:</td>
                        <td>{{ dateformatsimple($pr->DocDate) }}</td>
                    </tr>
                    <tr>
                        <td>Req Name</td>
                        <td>:</td>
                        <td>{{ ($pr->ReqName) }}</td>
                    </tr>
                    <tr>
                        <td>Branch</td>
                        <td>:</td>
                        <td>{{ ($branch->name) }} [{{$branch->code}}]</td>
                    </tr>
                    <tr>
                        <td>Store</td>
                        <td>:</td>
                        <td>{{ $store->name }} [{{ $store->code }}]</td>
                    </tr>
                    <tr>
                        <td>Doc. Status</td>
                        <td>:</td>
                        <td>{{ ucfirst($pr->doc_status) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- This is the main table that will break across pages -->
    <table class="border item-table">
        <thead>
            <tr>
                <th class="header-item" style="width: 5%;">No.</th>
                <th class="header-item" style="width: 50%;">Description</th>
                <th class="header-item" style="width: 15%;">Qty</th>
                <th class="header-item" style="width: 15%;">Unit</th>
                <th class="header-item" style="width: 15%;">VAT Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pr->items as $k => $v)
            <tr>
                <td style="text-align: center;">{{ ++$k }}.</td>
                <td>
                    {{ $v->item->name }} - {{ $v->item->sku }}
                </td>
                <td style="text-align: right;">{{ nominal($v->qty) }}</td>
                <td>{{ $v->item->unit_of_measurement }}</td>
                <td>{{ $v->item->vatcode }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    
    <div style="width:100%">
        <b>Keterangan : </b>
        <p>{!! nl2br($pr->Comments) !!}</p>
    </div>

    <hr>

    <div class="footer-note">
        <small>
            Document ini sah dan di proses oleh sistem. Silakan hubungi administrator apabila membutuhkan bantuan.
        </small>
    </div>

</body>
</html>
