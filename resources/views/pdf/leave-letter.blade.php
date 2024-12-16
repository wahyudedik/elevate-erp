<!DOCTYPE html>
<html>

<head>
    <title>SURAT PERMOHONAN CUTI</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 40px;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            color: #2c3e50;
        }

        .content {
            margin: 30px 0;
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin: 20px 0;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            padding: 0 50px;
        }

        .signature-box {
            width: 300px;
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .signature-line {
            border-bottom: 2px solid #2c3e50;
            margin: 50px auto 10px;
            width: 200px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        td {
            padding: 15px 12px;
            vertical-align: top;
        }

        .form-field {
            border-bottom: 2px solid #2c3e50;
            padding: 8px 0;
            min-width: 300px;
            transition: all 0.3s ease;
        }

        .form-field:hover {
            border-bottom-color: #3498db;
        }

        .checkbox-group {
            display: flex;
            gap: 30px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #2c3e50;
        }

        .checkbox-item label {
            font-size: 16px;
            color: #2c3e50;
        }

        h2,
        h3 {
            color: #2c3e50;
            letter-spacing: 1px;
        }

        p {
            margin: 8px 0;
            color: #2c3e50;
        }

        @media print {
            body {
                margin: 20px;
            }

            .content {
                box-shadow: none;
            }

            .signature-box {
                background-color: transparent;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>SURAT PERMOHONAN CUTI</h2>
        <h3>Nomor: ______/CUTI/{{ date('Y') }}</h3>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>

        <table>
            <tr>
                <td width="200">Nama</td>
                <td width="10">:</td>
                <td>
                    <div class="form-field"></div>
                </td>
            </tr>
            <tr>
                <td>Cabang</td>
                <td>:</td>
                <td>
                    <div class="form-field"></div>
                </td>
            </tr>
            <tr>
                <td>Tanggal Mulai Cuti</td>
                <td>:</td>
                <td>
                    <div class="form-field"></div>
                </td>
            </tr>
            <tr>
                <td>Tanggal Selesai Cuti</td>
                <td>:</td>
                <td>
                    <div class="form-field"></div>
                </td>
            </tr>
            <tr>
                <td>Alasan Cuti</td>
                <td>:</td>
                <td>
                    <div class="form-field"></div>
                </td>
            </tr>
            <tr>
                <td>Status</td>
                <td>:</td>
                <td>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="disetujui">
                            <label for="disetujui">Disetujui</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="ditolak">
                            <label for="ditolak">Ditolak</label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Catatan</td>
                <td>:</td>
                <td>
                    <div class="form-field"></div>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <p>Menyetujui,</p>
                <p>Kepala Cabang</p>
                <br><br><br><br>
                <p style="border-bottom: 1px solid black; display: inline-block; padding: 0 70px;"></p>
                <p>NIP. ________________</p>
            </td>
            <td style="width: 50%; text-align: center;">
                <p>Pemohon,</p>
                <p></p>
                <br><br><br><br><br>
                <p style="border-bottom: 1px solid black; display: inline-block; padding: 0 70px;"></p>
                <p>NIP. ________________</p>
            </td>
        </tr>
    </table>

</body>

</html>
