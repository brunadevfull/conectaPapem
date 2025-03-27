<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito</title>
      <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin: 100px;
        }

        h1 {
            color: #ff0000;
        }

        p {
            font-size: 18px;
            color: #333333;
        }
    </style>
</head>
<body>
    <?php include 'header.php'?>
    <h1>Acesso Restrito</h1>
    <p>Desculpe, mas esta página está disponível apenas para administradores.</p>
    <a href="index.php" class="button">Voltar à Página Inicial</a>
    <?php include 'footer.php'; ?>
</body>
</html>
