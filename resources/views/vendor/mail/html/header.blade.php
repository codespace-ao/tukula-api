@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
            <img src="https://i.ibb.co/NgFSNBGK/Group-20.png" alt="Tukula Logo" border="0" class="logo">
            @else
            {{ $slot }}
            @endif
        </a>
    </td>
</tr>