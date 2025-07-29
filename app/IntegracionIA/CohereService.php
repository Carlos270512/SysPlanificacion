<?php
class CohereService
{
    private $apiKey;
    private $apiUrl = 'https://api.cohere.ai/v1/generate';
    
    public function __construct()
    {
        // Aquí pondrás tu API key de Cohere cuando la obtengas
        $this->apiKey = 'S8Q0g1YYsa99d5T025xQOeTFJ1qy4b3deRJCUOAr'; // Lo cambiaremos después
    }
    
    public function generarRespuesta($pregunta)
    {
        $data = [
            'model' => 'command',
            'prompt' => $pregunta,
            'max_tokens' => 300,
            'temperature' => 0.7,
            'k' => 0,
            'stop_sequences' => [],
            'return_likelihoods' => 'NONE'
        ];
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Cohere-Version: 2022-12-06'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            curl_close($ch);
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . curl_error($ch)
            ];
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'Error del servidor: ' . $httpCode
            ];
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['generations'][0]['text'])) {
            return [
                'success' => true,
                'respuesta' => trim($result['generations'][0]['text'])
            ];
        }
        
        return [
            'success' => false,
            'message' => 'No se pudo generar una respuesta'
        ];
    }
    
    // NUEVO MÉTODO: Verificar si la API key está configurada
    public function isConfigured()
    {
        return $this->apiKey !== 'TU_API_KEY_AQUI' && !empty($this->apiKey);
    }
}
?>