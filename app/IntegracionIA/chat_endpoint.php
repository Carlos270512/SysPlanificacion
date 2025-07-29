<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/CohereService.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $pregunta = $input['pregunta'] ?? '';
    
    if (empty(trim($pregunta))) {
        echo json_encode(['success' => false, 'message' => 'La pregunta no puede estar vacía']);
        exit;
    }
    
    // Verificar si la API key está configurada
    $cohereService = new CohereService();
    
    // Si la API key no está configurada, simular respuesta para pruebas
    if (!$cohereService->isConfigured()) {
        // Modo de prueba - simular respuestas
        $respuestaSimulada = simularRespuestaIA($pregunta);
        echo json_encode([
            'success' => true,
            'respuesta' => $respuestaSimulada . "\n\n⚠️ *Modo de prueba - Configure su API key de Cohere para usar IA real*"
        ]);
        exit;
    }
    
    // Usar Cohere real
    $resultado = $cohereService->generarRespuesta($pregunta);
    echo json_encode($resultado);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}

// Función para simular respuestas mientras configuramos Cohere
function simularRespuestaIA($pregunta) {
    $pregunta = strtolower($pregunta);
    
    // Respuestas educativas simuladas
    if (strpos($pregunta, 'planificación') !== false || strpos($pregunta, 'planificar') !== false) {
        return "Una buena planificación educativa debe incluir:\n\n1. **Objetivos claros**: Define qué quieres que aprendan tus estudiantes\n2. **Metodología apropiada**: Elige estrategias que se adapten al contenido\n3. **Recursos necesarios**: Identifica materiales y herramientas\n4. **Evaluación**: Establece cómo medirás el aprendizaje\n5. **Cronograma**: Distribuye el tiempo de manera efectiva\n\n¿Te gustaría que profundice en algún aspecto específico?";
    }
    
    if (strpos($pregunta, 'estudiantes') !== false || strpos($pregunta, 'alumnos') !== false) {
        return "Para motivar a los estudiantes considera:\n\n• **Haz relevante el contenido**: Conecta con sus intereses\n• **Varía las metodologías**: Combina teoría y práctica\n• **Reconoce el progreso**: Celebra los logros\n• **Fomenta la participación**: Crea un ambiente seguro\n• **Usa tecnología**: Incorpora herramientas digitales\n\n¿Hay algún desafío específico con tus estudiantes?";
    }
    
    if (strpos($pregunta, 'evaluación') !== false || strpos($pregunta, 'evaluar') !== false) {
        return "La evaluación efectiva incluye:\n\n**Tipos de evaluación:**\n• Diagnóstica: Al inicio\n• Formativa: Durante el proceso\n• Sumativa: Al final\n\n**Herramientas:**\n• Rúbricas claras\n• Portafolios\n• Autoevaluación\n• Evaluación entre pares\n\n¿Qué tipo de evaluación necesitas diseñar?";
    }
    
    if (strpos($pregunta, 'metodología') !== false || strpos($pregunta, 'método') !== false) {
        return "Metodologías efectivas para el aprendizaje:\n\n**Activas:**\n• Aprendizaje basado en problemas\n• Trabajo colaborativo\n• Flipped classroom\n\n**Tradicionales mejoradas:**\n• Clase magistral interactiva\n• Estudio de casos\n• Demostraciones prácticas\n\n¿Para qué materia o nivel necesitas metodología?";
    }
    
    // Respuestas generales
    if (strpos($pregunta, 'monogamia') !== false) {
        return "La monogamia es un sistema de apareamiento en el que un individuo tiene una sola pareja durante un período determinado. En humanos, se refiere tradicionalmente al matrimonio entre dos personas que se comprometen exclusivamente entre sí.\n\n**Características:**\n• Compromiso exclusivo entre dos personas\n• Base de muchos sistemas familiares\n• Varía culturalmente en su interpretación\n• Puede ser serial (sucesiva) o de por vida\n\n¿Te interesa algún aspecto específico sobre este tema?";
    }
    
    if (strpos($pregunta, 'hola') !== false || strpos($pregunta, 'saludar') !== false) {
        return "¡Hola! 👋 Soy tu asistente de IA educativa. Estoy aquí para ayudarte con:\n\n• Planificación de clases\n• Estrategias de enseñanza\n• Evaluación de estudiantes\n• Recursos educativos\n• Cualquier pregunta general\n\n¿En qué puedo ayudarte hoy?";
    }
    
    // Respuesta por defecto
    return "Interesante pregunta. Como asistente educativo, puedo ayudarte con temas relacionados a:\n\n• **Planificación académica**\n• **Metodologías de enseñanza**\n• **Evaluación de estudiantes**\n• **Recursos educativos**\n• **Preguntas generales**\n\n¿Podrías ser más específico sobre lo que necesitas? Así podré darte una respuesta más útil para tu contexto educativo.";
}
?>