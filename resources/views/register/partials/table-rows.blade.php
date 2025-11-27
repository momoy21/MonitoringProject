@forelse($users as $user)
<tr class="editable-row" ondblclick="editPM({{ $user->id }})" title="Double-click untuk edit" style="cursor: pointer;">
    <td>
        <span class="pm-name" data-id="{{ $user->id }}">
            <div class="truncate-text">{{ $user->name }}</div>
        </span>
    </td>
    <td>
        <div class="truncate-text" title="{{ $user->email }}">
            {{ $user->email }}
        </div>
    </td>
    <td>
        @php
            $bidangJasaIds = $user->bidang_jasa_ids ? json_decode($user->bidang_jasa_ids, true) : [];
            $bidangJasas = \App\Models\BidangJasa::whereIn('id_bidjasa', $bidangJasaIds)->pluck('desc_bidjasa')->toArray();
        @endphp
        @if(count($bidangJasas) > 0)
            <div class="multiline-text">
                {{ implode(', ', $bidangJasas) }}
            </div>
        @else
            <span class="text-muted">Semua Bidang Jasa</span>
        @endif
    </td>
    <td>{{ $user->created_at->format('d/m/Y') }}</td>
    <td onclick="event.stopPropagation();">
        <div class="dropdown">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewPM({{ $user->id }})">
                    <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                    <i class="bx bx-trash me-1"></i> Hapus</a></li>
            </ul>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-4">
        <i class="bx bx-user-x" style="font-size: 3rem; opacity: 0.3;"></i>
        <p class="text-muted mt-2">Tidak ada data yang sesuai dengan pencarian</p>
    </td>
</tr>
@endforelse
