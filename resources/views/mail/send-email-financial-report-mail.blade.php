<x-mail::message>
    # Introduction

    <x-mail::table>
        | Field | Value |
        |-------|-------|
        | Report Name | {{ $financialReport->report_name }} |
        | Report Type | {{ $financialReport->report_type }} |
        | Report Period Start | {{ $financialReport->report_period_start }} |
        | Report Period End | {{ $financialReport->report_period_end }} |
        | Notes | {{ $financialReport->notes }} |
        | Email | {{ $financialReport->email }} |
        | Message | {{ $financialReport->message }} |
    </x-mail::table>

    @if (!empty($financialReport->balanceSheet))
        <x-mail::table>
            | Balance Sheet | Value |
            |---------------|-------|
            @foreach ($financialReport->balanceSheet as $balanceSheet)
                | Total Assets (Entry {{ $loop->iteration }}) | {{ number_format($balanceSheet['total_assets'], 2) }} |
                | Total Liabilities (Entry {{ $loop->iteration }}) |
                {{ number_format($balanceSheet['total_liabilities'], 2) }} |
                | Total Equity (Entry {{ $loop->iteration }}) | {{ number_format($balanceSheet['total_equity'], 2) }}
                |
                @if (!$loop->last)
                    |---------------|-------|
                @endif
            @endforeach
        </x-mail::table>
    @else
        <p>No Balance Sheet data available.</p>
    @endif

    @if (!empty($financialReport->incomeStatement))
        <x-mail::table>
            | Income Statement | Value |
            |------------------|-------|
            @foreach ($financialReport->incomeStatement as $incomeStatement)
                | Total Revenue (Entry {{ $loop->iteration }}) |
                {{ number_format($incomeStatement['total_revenue'], 2) }}
                |
                | Total Expenses (Entry {{ $loop->iteration }}) |
                {{ number_format($incomeStatement['total_expenses'], 2) }} |
                | Net Income (Entry {{ $loop->iteration }}) | {{ number_format($incomeStatement['net_income'], 2) }} |
                @if (!$loop->last)
                    |------------------|-------|
                @endif
            @endforeach
        </x-mail::table>
    @else
        <p>No Income Statement data available.</p>
    @endif

    @if (!empty($financialReport->cashFlow))
        <x-mail::table>
            | Cash Flow | Value |
            |-----------|-------|
            @foreach ($financialReport->cashFlow as $cashFlow)
                | Operating Cash Flow (Entry {{ $loop->iteration }}) |
                {{ number_format($cashFlow['operating_cash_flow'], 2) }} |
                | Investing Cash Flow (Entry {{ $loop->iteration }}) |
                {{ number_format($cashFlow['investing_cash_flow'], 2) }} |
                | Financing Cash Flow (Entry {{ $loop->iteration }}) |
                {{ number_format($cashFlow['financing_cash_flow'], 2) }} |
                | Net Cash Flow (Entry {{ $loop->iteration }}) | {{ number_format($cashFlow['net_cash_flow'], 2) }} |
                @if (!$loop->last)
                    |-----------|-------|
                @endif
            @endforeach
        </x-mail::table>
    @else
        <p>No Cash Flow data available.</p>
    @endif

    {{-- <x-mail::button :url="''">
Button Text
</x-mail::button> --}}

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
