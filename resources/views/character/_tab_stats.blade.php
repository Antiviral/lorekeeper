@if (!$character->is_myo_slot)
    <div class="row">
        <div class="col-lg-3 col-4">
            <h5>Category</h5>
        </div>
        <div class="col-lg-9 col-8">{!! $character->category->displayName !!}</div>
    </div>
@endif

<div class="row">
    <div class="col-lg-3 col-4">
        <h5>Species</h5>
    </div>
    <div class="col-lg-9 col-8" id="pokemon-species">{!! $character->pokemonSpecies !!}</div>
</div>

<div class="row">
    <div class="col-lg-3 col-4">
        <h5>Type(s)</h5>
    </div>
    <div class="col-lg-9 col-8" id="pokemon-types"></div>
</div>

<hr />

<div class="row">
    <div class="col-lg-3 col-4">
        <h5>Owner</h5>
    </div>
    <div class="col-lg-9 col-8">{!! $character->displayOwner !!}</div>
</div>

<div class="row">
    <div class="col-lg-3 col-4">
        <h5 class="mb-0">Created</h5>
    </div>
    <div class="col-lg-9 col-8">{!! format_date($character->created_at) !!}</div>
</div>

<hr />

<h5>
    <i class="text-{{ $character->is_giftable ? 'success far fa-circle' : 'danger fas fa-times' }} fa-fw mr-2"></i> {{ $character->is_giftable ? 'Can' : 'Cannot' }} be gifted
</h5>
<h5>
    <i class="text-{{ $character->is_tradeable ? 'success far fa-circle' : 'danger fas fa-times' }} fa-fw mr-2"></i> {{ $character->is_tradeable ? 'Can' : 'Cannot' }} be traded
</h5>
<h5>
    <i class="text-{{ $character->is_sellable ? 'success far fa-circle' : 'danger fas fa-times' }} fa-fw mr-2"></i> {{ $character->is_sellable ? 'Can' : 'Cannot' }} be sold
</h5>
@if ($character->sale_value > 0)
    <div class="row">
        <div class="col-lg-3 col-4">
            <h5>Sale Value</h5>
        </div>
        <div class="col-lg-9 col-8">
            {{ Config::get('lorekeeper.settings.currency_symbol') }}{{ $character->sale_value }}
        </div>
    </div>
@endif
@if ($character->transferrable_at && $character->transferrable_at->isFuture())
    <div class="row">
        <div class="col-lg-3 col-4">
            <h5>Cooldown</h5>
        </div>
        <div class="col-lg-9 col-8">Cannot be transferred until {!! format_date($character->transferrable_at) !!}</div>
    </div>
@endif
@if (Auth::check() && Auth::user()->hasPower('manage_characters'))
    <div class="mt-3">
        <a href="#" class="btn btn-outline-info btn-sm edit-stats" data-{{ $character->is_myo_slot ? 'id' : 'slug' }}="{{ $character->is_myo_slot ? $character->id : $character->slug }}"><i class="fas fa-cog"></i> Edit</a>
    </div>
@endif

<script>
    async function getPokemonTypes(species) {
        try {
            const response = await fetch(`https://pokeapi.co/api/v2/pokemon/${species.toLowerCase()}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();

            // Extract and capitalize the types
            const types = data.types.map(typeInfo => typeInfo.type.name.charAt(0).toUpperCase() + typeInfo.type.name.slice(1));

            // Display the types
            displayTypes(types);
        } catch (error) {
            console.error('Fetch error: ', error);
        }
    }

    function displayTypes(types) {
        const typesContainer = document.getElementById('pokemon-types');
        if (types.length === 1) {
            typesContainer.textContent = types[0];
        } else if (types.length > 1) {
            typesContainer.textContent = types.join('/');
        } else {
            typesContainer.textContent = 'Unknown';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Get the species name from the server-side rendered element
        const speciesElement = document.getElementById('pokemon-species');
        const species = speciesElement ? speciesElement.textContent.trim() : '';
        if (species) {
            getPokemonTypes(species);
        } else {
            console.error('No species found');
        }
    });
</script>
