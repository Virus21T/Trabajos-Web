<?php
require_once 'config/database.php';

try {
    // Actualizar Empanada de Pescado
    $stmt = $conn->prepare("UPDATE productos SET descripcion = ? WHERE nombre = ?");
    $stmt->execute(['Deliciosa empanada rellena de pescado fresco con cebolla y especias', 'Empanada de Pescado']);
    
    // Actualizar Empanada de Lentejas
    $stmt->execute(['Empanada vegetariana rellena de lentejas y verduras', 'Empanada de Lentejas']);
    
    // Actualizar Empanada de Maíz
    $stmt->execute(['Empanada dulce rellena de maíz tierno y queso', 'Empanada de Maíz']);
    
    echo "Descripciones actualizadas correctamente";
} catch (Exception $e) {
    echo "Error al actualizar las descripciones: " . $e->getMessage();
}
?> 