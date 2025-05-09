<table class="table">
    <tbody>
        @foreach ($products as $item)
            <tr>
                <td>
                    {{ $item->name }} - {{ $item->sku }} <br/>{{ $item->web_error_code ?? '' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>