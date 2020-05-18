<table>
    <thead>            
        <tr>
            <th>Tipo</th>
            <th>Tipo Estado</th>
            <th>Subtipo</th>
            <th>Subtipo Estado</th>
            <th>Item</th>
            <th>Item Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{$item->type_name}}</td>
                <td>{{$item->type_status}}</td>
                <td>{{$item->subtype_name}}</td>
                <td>{{$item->subtype_status}}</td>
                <td>{{$item->item_name}}</td>
                <td>{{$item->item_status}}</td>                   
            </tr>
        @endforeach
    </tbody>
</table>
