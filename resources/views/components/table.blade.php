

<div>
    <div @class(['flex', 'flex-col', 'mt-5'])>
        <div @class(['-m-1.5', 'overflow-x-auto'])>
            <div @class(['p-1.5', 'min-w-full', 'inline-block', 'align-middle'])>
                <div @class([
                    'border',
                    'border-gray-200',
                    'rounded-lg',
                    'overflow-hidden',
                    'dark:border-neutral-700',
                ])>
                    <table @class([
                        'min-w-full',
                        'divide-y',
                        'divide-gray-200',
                        'dark:divide-neutral-700',
                    ])>
                        <thead @class(['bg-gray-50', 'dark:bg-neutral-700'])>
                            <tr>
                                {{ $thead }}
                            </tr>
                        </thead>
                        <tbody @class(['divide-y', 'divide-gray-200', 'dark:divide-neutral-700'])>
                            {{ $tbody }}

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div @class(['mt-4'])>
        {{$paginate ?? ''}}
    </div>
    <!-- No surplus words or unnecessary actions. - Marcus Aurelius -->
</div>