@props([
   'class' => '',
   'colspan' => '',
   'name' => ''
])
    <td colspan="{{ $colspan }}"
                                            class="{{ $class }} px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200" {{ $attributes }} >
                                            @if (!$name)
                                             {{ $slot }}
                                          @else                                               
   {{ $name }}

  @endif
                                        </td>

                                      