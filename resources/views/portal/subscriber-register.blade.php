<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redirecting... | 3DHub Data Portal</title>
  <script>
    // subscriber-{{ route('register') }} is no longer used. All users now register as Registered Users via {{ route('register') }}.
    window.location.replace('{{ route('register') }}' + window.location.search);
  </script>
</head>
<body></body>
</html>
