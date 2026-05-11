<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM GOOD RECEIPT</title>
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
        .item-table {
            page-break-inside: auto;
        }

        .item-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

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

    <h3 style="text-align:center">FORM GOOD RECEIPT</h3>

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
                        <td>Good Receipt No.</td>
                        <td>:</td>
                        <td>{{ $gr->U_SOL_SYNC_KEY }}</td>
                    </tr>
                    <tr>
                        <td>Doc. Date</td>
                        <td>:</td>
                        <td>{{ dateformatsimple($gr->DocDate) }}</td>
                    </tr>
                    <tr>
                        <td>Reff. PR</td>
                        <td>:</td>
                        <td>{{ $gr->U_SOL_REF_KEY }}</td>
                    </tr>
                    <tr>
                        <td>Store</td>
                        <td>:</td>
                        <td>{{ $store->name }} [{{ $store->code }}]</td>
                    </tr>
                    @if ($vendor)
                    <tr>
                        <td>Vendor Name</td>
                        <td>:</td>
                        <td>{{ $vendor->name }}</td>
                    </tr>
                    <tr>
                        <td>Vendor Code</td>
                        <td>:</td>
                        <td>{{ $vendor->code }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Doc. Status</td>
                        <td>:</td>
                        <td>{{ ucfirst($gr->doc_status) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <table class="border item-table">
        <thead>
            <tr>
                <th class="header-item" style="width: 5%;">No.</th>
                <th class="header-item" style="width: 15%;">Item Code</th>
                <th class="header-item" style="width: 50%;">Description</th>
                <th class="header-item" style="width: 15%;">Unit</th>
                <th class="header-item" style="width: 15%;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gr->items as $k => $v)
            <tr>
                <td style="text-align: center;">{{ ++$k }}.</td>
                <td>{{ $v->ItemCode }}</td>
                <td>
                    {{ $v->item->name }}
                </td>
                <td style="text-align: right;">{{ nominal($v->Quantity) }}</td>
                <td>{{ $v->item->unit_of_measurement }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    
    <div style="width:100%">
        <b>Keterangan : </b>
        <p>{!! nl2br($gr->Comments) !!}</p>
    </div>

    <hr>

    <div class="footer-note">
        <small>
            Document ini sah dan di proses oleh sistem. Silakan hubungi administrator apabila membutuhkan bantuan.
        </small>
    </div>

</body>
</html>
