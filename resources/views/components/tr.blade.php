@props([
    'class' => ''
  ])
                                <tr 
                                class=" {{ $class }} relative group hover:bg-gray-50 dark:hover:bg-neutral-800 transition" {{$attributes}} >
 {{ $slot }}
                            </tr>