@props([
    'name',
    'id' => null,
    'value' => null,
    'selected' => null,
    'options' => [],
    'placeholder' => 'Pilih opsi...',
    'required' => false,
    'disabled' => false,
    'buttonClass' => '',
    'panelClass' => '',
    'containerClass' => '',
    'autoSubmit' => false,
])

<x-dropdown
    :name="$name"
    :id="$id"
    :value="$value"
    :selected="$selected"
    :options="$options"
    :placeholder="$placeholder"
    :required="$required"
    :disabled="$disabled"
    :button-class="$buttonClass"
    :panel-class="$panelClass"
    :container-class="$containerClass"
    :auto-submit="$autoSubmit"
    {{ $attributes }}
>
    {{ $slot ?? '' }}
</x-dropdown>

