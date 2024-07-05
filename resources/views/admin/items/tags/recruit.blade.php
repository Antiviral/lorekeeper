<h1>Recruit Slot Settings</h1>

<h3>Basic Information</h3>
<div class="form-group">
    {!! Form::label('Possible Pokemon') !!} 
    {!! Form::textarea('name', $tag->getData()['name'], ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    @if ($isMyo)
        {!! add_help('This section is for making additional notes about the MYO slot. If there are restrictions for the character that can be created by this slot that cannot be expressed with the options below, use this section to describe them.') !!}
    @else
        {!! add_help('This section is for making additional notes about the character and is separate from the character\'s profile (this is not editable by the user).') !!}
    @endif
    {!! Form::textarea('description', $tag->getData()['description'], ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('is_visible', 1, $tag->getData()['is_visible'], ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
        'Turn this off to hide the ' . ($isMyo ? 'MYO slot' : 'character') . '. Only mods with the Manage Masterlist power (that\'s you!) can view it - the owner will also not be able to see the ' . ($isMyo ? 'MYO slot' : 'character') . '\'s page.',
    ) !!}
</div>

<h3>Transfer Information</h3>

<div class="alert alert-info">
    These are displayed on the MYO slot's profile, but don't have any effect on site functionality except for the following:
    <ul>
        <li>If all switches are off, the MYO slot cannot be transferred by the user (directly or through trades).</li>
        <li>If a transfer cooldown is set, the MYO slot also cannot be transferred by the user (directly or through trades) until the cooldown is up.</li>
    </ul>
</div>
<div class="form-group">
    {!! Form::checkbox('is_giftable', 1, $tag->getData()['is_giftable'], ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_giftable', 'Is Giftable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_tradeable', 1, $tag->getData()['is_tradeable'], ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_tradeable', 'Is Tradeable', ['class' => 'form-check-label ml-3']) !!}
</div>

@section('scripts')
    @parent
    @include('widgets._character_create_options_js')
@endsection
