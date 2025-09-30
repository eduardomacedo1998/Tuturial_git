<?php if (!empty($usuarios)) : ?>
        <h2>Dados dos produtos encontrados:</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
            </tr>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td  id="id"><?php echo $usuario['id']; ?></td>
                    <td><?php echo $usuario['nome']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST") : ?>
        <p>Nenhum produto encontrado com esse nome.</p>
    <?php endif; 
    
    
    ?>