<ul class="text-center">
    <li class="sidebar-header"><a href="#" class="card-link">Featured Pokemon</a></li>

    <li class="sidebar-section p-2">
        @if (isset($featured) && $featured)
            <div>
                <a href="{{ $featured->url }}"><img src="{{ $featured->image->thumbnailUrl }}" class="img-thumbnail" /></a>
            </div>
            <div class="mt-1">
                <a href="{{ $featured->url }}" class="h5 mb-0">
                    @if (!$featured->is_visible)
                        <i class="fas fa-eye-slash"></i>
                    @endif {{ $featured->fullName }}
                </a>
            </div>
            <div class="small">
                {!! $featured->pokemon_species ? $featured->pokemon_species : 'Error' !!}<br />{!! $featured->displayOwner !!}
            </div>
        @else
            <p>There is no featured pokemon.</p>
        @endif
    </li>
</ul>
