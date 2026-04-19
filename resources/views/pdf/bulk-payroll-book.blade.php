<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Livre de Paie</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.3;
        }
        .page-break {
            page-break-after: always;
        }
        .w-full { width: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-sans { font-family: sans-serif; }
        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
        .text-2xl { font-size: 1.5rem; }
        .uppercase { text-transform: uppercase; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .border-collapse { border-collapse: collapse; }
        .border { border-width: 1px; border-style: solid; border-color: #d1d5db; }
        .p-2 { padding: 0.5rem; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .whitespace-nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    @foreach ($employeeChunks as $chunk)
        @include('pdf.payroll-book-content', [
            'status' => $payrollBook->status,
            'company' => $company,
            'listEmployee' => $chunk,
            'matrix' => $matrix,
            'employeeContribution' => $employeeContribution,
            'employerContribution' => $employerContribution,
            'retenues' => $retenues,
            'salaries' => $salaries,
            'showPagination' => false,
        ])

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

   
</body>
</html>