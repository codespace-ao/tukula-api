@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Tukula')
            <img src="https://i.postimg.cc/9FgsyrgJ/Group-20.png" alt="Tukula Logo" border="0" class="logo">
            @else
            {{ $slot }}
            @endif
        </a>
    </td>
</tr>