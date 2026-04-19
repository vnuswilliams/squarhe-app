<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulletins de Paie - {{ now()->format('m/Y') }}</title>
     <style>
        @page {
            margin: 0.5cm; /* Very tight margins for single page */
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1a1a1a; /* zinc-900 */
            line-height: 1.1;
        }
        /* Layout Utilities */
        .w-full { width: 100%; }
        .grid { display: table; width: 100%; table-layout: fixed; }
        .grid-cols-2 { display: table; width: 100%; }
        .col-span-1 { display: table-cell; vertical-align: top; }
        
        /* Simulating Grid with floats/inline-block for PDF if needed, but Tables are safer */
        .layout-table { width: 100%; border-collapse: collapse; }
        .layout-row { width: 100%; }
        .layout-col { vertical-align: top; }
        .layout-col-1-2 { width: 50%; }
        .layout-col-1-4 { width: 25%; }
        
        /* Spacing */
        .p-6 { padding: 15px; }
        .p-4 { padding: 10px; }
        .pt-0 { padding-top: 0; }
        .mb-6 { margin-bottom: 15px; }
        .mb-1 { margin-bottom: 4px; }
        .gap-4 { /* Gap doesn't work in tables, handled via padding */ }
        
        /* Typography */
        .text-3xl { font-size: 20px; font-weight: bold; }
        .text-xl { font-size: 14px; font-weight: bold; }
        .text-lg { font-size: 12px; font-weight: bold; }
        .text-sm { font-size: 9px; }
        .text-xs { font-size: 8px; }
        .font-bold { font-weight: bold; }
        .font-semibold { font-weight: 600; }
        .font-medium { font-weight: 500; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .uppercase { text-transform: uppercase; }
        .tracking-tight { letter-spacing: -0.025em; }
        
        /* Colors - Mimicking Tailwind Palette */
        .bg-blue-600 { background-color: #2563eb; color: white !important; }
        .text-white { color: white; }
        .text-blue-600 { color: #2563eb; }
        
        .bg-zinc-50 { background-color: #f9fafb; }
        .bg-zinc-100 { background-color: #f3f4f6; }
        .bg-zinc-800 { background-color: #27272a; color: white; }
        .bg-zinc-900 { background-color: #18181b; }
        
        .text-zinc-400 { color: #a1a1aa; }
        .text-zinc-500 { color: #71717a; }
        .text-zinc-900 { color: #18181b; }
        .text-red-600 { color: #dc2626; }
        
        .border-zinc-200 { border-color: #e4e4e7; }
        
        /* Borders */
        .border { border: 1px solid #e5e7eb; }
        .border-t { border-top: 1px solid #e5e7eb; }
        .border-b { border-bottom: 1px solid #e5e7eb; transparent; }
        .rounded-xl { border-radius: 8px; }
        .rounded-lg { border-radius: 6px; }
        .overflow-hidden { overflow: hidden; }
        
        /* Table Styles */
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th { background-color: #f9fafb; color: #6b7280; font-weight: 600; text-align: left; padding: 6px 8px; font-size: 8px; }
        table.data-table td { border-bottom: 1px solid #f3f4f6; padding: 6px 8px; font-size: 9px; color: #374151; }
        table.data-table tr:last-child td { border-bottom: none; }
        
        /* Specific Overrides for PDF Compactness */
        h1, h2, h3, h4, p { margin: 0; padding: 0; }
        .leading-relaxed { line-height: 1.4; }
        .space-y-1 > * + * { margin-top: 2px; }
        
        /* Utility to force whitespace handling */
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body>
    @foreach($employees as $index => $employee)
        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            @include('pdf.payslip-content', ['employee' => $employee])
        </div>
    @endforeach
</body>
</html>
