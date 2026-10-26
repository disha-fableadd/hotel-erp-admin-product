<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GSTR-3B Report</title>
    <style>
        body { font-family: Courier, monospace; font-size: 12px; }
        pre { font-family: Courier, monospace; font-size: 12px; }
    </style>
</head>
<body>
<pre>
Fablead                                   PAGE NO. 1
Regular DATE : FROM {{ $data['from_date'] }} TO {{ $data['to_date'] }}
GSTIN : 

GSTR-3B MONTHLY RETURN
-------------------------------------------------------------------------------
BOOK                  TAXABLE.VALUE     IGST PAYABLE     CGST PAYABLE     SGST PAYABLE
-------------------------------------------------------------------------------

STATEWISE - UNREGISTERED AND COMPOSITION
-------------------------------------------------------------------------------
TOTAL SALES :         {{ $data['total_sales'] }}          0.00             0.00             0.00

SALES RETURN          {{ $data['sales_return'] }}         0.00             0.00             0.00
SALES RETURN UN-REG   0.00                                0.00             0.00             0.00
CREDIT NOTE ( - )     0.00                                0.00             0.00             0.00
CREDIT NOTE UN-REG    0.00                                0.00             0.00             0.00
DEBIT NOTE ( + )      0.00                                0.00             0.00             0.00
OTHER INCOME          0.00                                0.00             0.00             0.00
-------------------------------------------------------------------------------
TOTAL PAYABLE         0.00                                0.00             0.00             0.00
-------------------------------------------------------------------------------

RCM PURCHASE BOOK
-------------------------------------------------------------------------------
RCM PURCHASE BOOK     0.00                                0.00             0.00             0.00
-------------------------------------------------------------------------------

PURCHASE (ALL)
-------------------------------------------------------------------------------
BOOK                  TAXABLE.VALUE     IGST RECEIVABLE  CGST RECEIVABLE  SGST RECEIVABLE
-------------------------------------------------------------------------------
TOTAL PURCHASE        {{ $data['purchase'] }}             0.00             0.00             0.00
-------------------------------------------------------------------------------
DEBIT NOTE ( - )      0.00                                0.00             0.00             0.00
CREDIT NOTE ( + )     0.00                                0.00             0.00             0.00
-------------------------------------------------------------------------------
TOTAL RECEIVABLE      0.00                                0.00             0.00             0.00
===============================================================================

NET PAYABLE/RECEIVABLE :-      {{ $data['net_payable'] }} 
===============================================================================

BALANCE :- 0
</pre>
</body>
</html>
