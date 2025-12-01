<?php
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid URL.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $url = $data['url'] ?? '';

    if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
        $videoId = '';
        // Regex to find video ID from various YouTube URL formats
        $regex = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        
        if (preg_match($regex, $url, $matches)) {
            $videoId = $matches[1];
        }

        if (!empty($videoId)) {
            $iframe = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . htmlspecialchars($videoId) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            $response['status'] = 'success';
            $response['iframe'] = $iframe;
            unset($response['message']);
        } else {
            $response['message'] = 'Could not extract YouTube video ID from the URL.';
        }
    }
}

echo json_encode($response);
