@props([
    'class' => '',
    'name' => ''
    
])

<th scope="col" @class([
    'px-6',
    'py-3',
    'text-start',
    'text-xs',
    'font-medium',
    'text-gray-500',
    'uppercase',
    'dark:text-neutral-400',
    'cursor-pointer',
     $class
        ])  {{ $attributes }}>
        @if (!empty($name))
            {{ $name }}
            @else
            {{ $slot }}
            @endif
</th>