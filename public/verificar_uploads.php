<?php
// verificar_imagens_atual.php
echo "<h3>🔍 Verificando Onde Estão as Imagens Agora</h3>";

$imagens = [
    '691cfc060c2de_20251119.jpg',
    '691bcb62bdf42_20251118.jpg', 
    '691cf8a054aee_20251118.jpg'
];

foreach ($imagens as $imagem) {
    echo "<br><strong>Verificando: {$imagem}</strong><br>";
    
    // Caminho CORRETO (deveria estar aqui)
    $caminhoCorreto = $_SERVER['DOCUMENT_ROOT'] . '/site/public/uploads/imagens/' . $imagem;
    echo "Caminho correto: " . (file_exists($caminhoCorreto) ? '✅ EXISTE' : '❌ NÃO EXISTE') . "<br>";
    
    // Caminho ATUAL (onde está sendo procurado)
    $caminhoAtual = $_SERVER['DOCUMENT_ROOT'] . '/site/uploads/imagens/' . $imagem;
    echo "Caminho atual: " . (file_exists($caminhoAtual) ? '✅ EXISTE' : '❌ NÃO EXISTE') . "<br>";
    
    // Caminho com ../
    $caminhoRelativo = $_SERVER['DOCUMENT_ROOT'] . '/site/public/../uploads/imagens/' . $imagem;
    echo "Caminho com ../: " . (file_exists($caminhoRelativo) ? '✅ EXISTE' : '❌ NÃO EXISTE') . "<br>";
}
?>