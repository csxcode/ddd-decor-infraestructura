<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body style="font-size: 12px;font-family: Arial, Helvetica, sans-serif;">

<p>Estimado proveedor:</p>
<p>Lo invitamos a revisar y cotizar la siguiente OT (orden de trabajo)</p>

<div>
    <table cellpadding="10" cellspacing="0" border="1" style="table-layout: fixed;margin-top: 20px;margin-bottom: 20px; border: 1px solid #171c21">
        <thead style="background-color: #242A30;font-weight: bold;">
        <tr>
            <th colspan="2" style="text-align: left;color:#fff">Detalles</th>
        </tr>
        </thead>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Número OT:</td>
            <td>{{ $data->wo_number }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Sede:</td>
            <td>
                {{ $data->branch_location_name }}
                <br>
                {{ $data->branch_location_address }}
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Personas de contacto:</td>
            <td>
                @foreach($contacts as $contact)
                    {{ $contact->first_name.' '.$contact->last_name }}
                    @if($contact->phone != null && $contact->phone != '')
                        <br>
                        {{ $contact->phone }}
                    @endif
                    <br>
                    <br>
                @endforeach
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;background-color: #242A30;width: 140px;font-weight: bold;color:#fff">Requerimiento:</td>
            <td>{{ $data->work_specs }}</td>
        </tr>
    </table>

    <x-email.footer/>

</div>
</body>
</html>
