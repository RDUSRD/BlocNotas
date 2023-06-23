<?php
// Ruta de la carpeta de notas
$notesDir = 'notes';

// Obtener la ruta completa actual
$currentPath = isset($_GET['path']) ? $_GET['path'] : '';

// Obtener la ruta completa de la carpeta actual
$currentFolder = $notesDir . '/' . $currentPath;

// Función para obtener la lista de carpetas
function getFoldersList($path)
{
    $folders = glob($path . '/*', GLOB_ONLYDIR);
    return $folders;
}

// Función para obtener la lista de notas
function getNotesList($path)
{
    $notes = glob($path . '/*.txt');
    return $notes;
}

// Función para crear una carpeta
function createFolder($path, $name)
{
    $folderPath = $path . '/' . $name;
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
}

// Función para eliminar una carpeta
function deleteFolder($path)
{
    if (is_dir($path)) {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                deleteFolder($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($path);
    }
}

// Función para editar el nombre de una carpeta
function renameFolder($path, $newName)
{
    $newPath = dirname($path) . '/' . $newName;
    if (!is_dir($newPath)) {
        rename($path, $newPath);
    }
}

// Función para crear una nota
function createNote()
{
    global $currentFolder, $notesDir;
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    if (!empty($name) && !empty($content)) {
        $notePath = $currentFolder . '/' . $name . '.txt';
        file_put_contents($notePath, $content);
    }
}

// Función para eliminar una nota
function deleteNote()
{
    global $currentFolder;
    $note = isset($_GET['delete']) ? $_GET['delete'] : '';
    $notePath = $currentFolder . '/' . $note;
    if (is_file($notePath)) {
        unlink($notePath);
    }
}

// Función para editar el nombre de una nota
function renameNote()
{
    global $currentFolder;
    $note = isset($_POST['note']) ? $_POST['note'] : '';
    $newName = isset($_POST['name']) ? $_POST['name'] : '';
    $notePath = $currentFolder . '/' . $note;
    $newPath = $currentFolder . '/' . $newName;
    if (!empty($note) && !empty($newName) && is_file($notePath) && !file_exists($newPath)) {
        rename($notePath, $newPath);
    }
}

// Función para obtener el contenido de una nota
function getNoteContent()
{
    global $currentFolder;
    $note = isset($_GET['file']) ? $_GET['file'] : '';
    $notePath = $currentFolder . '/' . $note;
    if (is_file($notePath)) {
        return file_get_contents($notePath);
    }
    return '';
}

// Función para obtener la ruta anterior
function getParentPath()
{
    global $currentPath;
    $parentPath = dirname($currentPath);
    if ($parentPath == '.') {
        $parentPath = '';
    }
    return $parentPath;
}

// Función para obtener la ruta absoluta de una carpeta
function getAbsolutePath($path)
{
    global $notesDir;
    return $notesDir . '/' . $path;
}

// Manejar las acciones
if (isset($_POST['create'])) {
    if (isset($_POST['type'])) {
        if ($_POST['type'] === 'folder') {
            createFolder($currentFolder, $_POST['name']);
        } else {
            createNote();
        }
    }
} elseif (isset($_POST['rename'])) {
    if (isset($_POST['type'])) {
        if ($_POST['type'] === 'folder') {
            renameFolder($currentFolder, $_POST['name']);
        } else {
            renameNote();
        }
    }
} elseif (isset($_GET['delete'])) {
    if (is_dir($currentFolder . '/' . $_GET['delete'])) {
        deleteFolder($currentFolder . '/' . $_GET['delete']);
    } else {
        deleteNote();
    }
}

function editNote()
{
    global $currentFolder;
    $note = isset($_GET['file']) ? $_GET['file'] : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $notePath = $currentFolder . '/' . $note;
    if (!empty($note) && !empty($content) && is_file($notePath)) {
        file_put_contents($notePath, $content);
        echo 'Nota editada.';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Bloc de Notas</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* Estilos personalizados para el navbar */
        .navbar {
            background-color: #f8f8f8;
            padding: 10px 0;
        }

        .navbar-brand {
            font-size: 30px;
            font-weight: bold;
            text-align: center;
            margin: 2;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <h2 class="navbar-brand">RDUS's Notes</h2>
    </div>

    <div class="container">
        <div class="directory">
            <h2>Directorio de Notas</h2>
            <ul>
                <?php if ($currentPath != '') : ?>
                    <li class="folder">
                        <a href="?path=<?php echo getParentPath(); ?>"><i class="fas fa-arrow-up"></i> Atrás</a>
                    </li>
                <?php endif; ?>
                <?php foreach (getFoldersList($currentFolder) as $folder) : ?>
                    <li class="folder">
                        <a href="?path=<?php echo $currentPath . '/' . basename($folder); ?>"><i class="fas fa-folder"></i> <?php echo basename($folder); ?></a>
                        <a class="delete" href="?delete=<?php echo $currentPath . '/' . basename($folder); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar esta carpeta?')"><i class="fas fa-trash"></i></a>
                        <a class="rename" href="#" onclick="renameFolder('<?php echo $currentPath . '/' . basename($folder); ?>')"><i class="fas fa-edit"></i></a>
                    </li>
                <?php endforeach; ?>
                <?php foreach (getNotesList($currentFolder) as $note) : ?>
                    <li class="note">
                        <a href="?file=<?php echo $currentPath . '/' . basename($note); ?>"><i class="fas fa-file-alt"></i> <?php echo basename($note); ?></a>
                        <a class="delete" href="?delete=<?php echo $currentPath . '/' . basename($note); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar esta nota?')"><i class="fas fa-trash"></i></a>
                        <a class="rename" href="#" onclick="renameNote('<?php echo $currentPath . '/' . basename($note); ?>')"><i class="fas fa-edit"></i></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form method="post" action="">
                <h3>Crear Carpeta</h3>
                <input type="text" name="name" placeholder="Nombre de la carpeta" required>
                <input type="hidden" name="type" value="folder">
                <input type="hidden" name="path" value="<?php echo $currentPath; ?>">
                <button type="submit" name="create"><i class="fas fa-plus"></i> Crear</button>
            </form>
        </div>
        <div class="editor">
            <?php if (isset($_GET['file'])) : ?>
                <?php $currentFile = $_GET['file']; ?>
                <?php $currentContent = getNoteContent(); ?>
                <h2>Editar Nota: <?php echo basename($currentFile); ?></h2>
            <?php else : ?>
                <?php $currentFile = ''; ?>
                <?php $currentContent = ''; ?>
                <h2>Nueva Nota</h2>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="name" value="<?php echo basename($currentFile, '.txt'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Contenido:</label>
                    <textarea name="content" required><?php echo $currentContent; ?></textarea>
                </div>
                <input type="hidden" name="type" value="note">
                <input type="hidden" name="path" value="<?php echo $currentPath; ?>">
                <?php if (isset($_GET['file'])) : ?>
                    <button type="submit" name="edit"><i class="fas fa-edit"></i> Editar</button>
                    <a class="delete" href="?delete=<?php echo $currentFile; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar esta nota?')"><i class="fas fa-trash"></i> Eliminar</a>
                <?php else : ?>
                    <button type="submit" name="create"><i class="fas fa-plus"></i> Crear</button>
                <?php endif; ?>
            </form>


        </div>
    </div>
    <script src="https://kit.fontawesome.com/4db0b56629.js" crossorigin="anonymous"></script>
</body>

</html>