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
    'shadow' => true,
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
    :shadow="$shadow"
    {{ $attributes }}
>
    {{ $slot ?? '' }}
</x-dropdown>

