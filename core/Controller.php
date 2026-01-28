<?php

class Controller
{
    /**
     * Charger une vue
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        
        $viewFile = ROOT . '/app/views/' . $view . '.php';
        
        if (!file_exists($viewFile)) 
        {
            // Créer le dossier parent si nécessaire
            $dir = dirname($viewFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Créer le fichier depuis le template
            $content = file_get_contents(ROOT . '/core/template/base_page.php');
            $content = str_replace('{{ path_file }}', "$view.php", $content);
            file_put_contents($viewFile, $content);
        }
        
        ob_start();
        require_once $viewFile;
        $content = ob_get_clean();
        $this->html($content);
    }

    /**
     * Redirection
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Réponse JSON
     */
    protected function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Réponse HTML
     */
    protected function html(string $html): void
    {
        header('Content-Type: text/html');
        echo $html;
        exit;
    }

    /**
     * Réponse Texte brut
     */
    protected function text(string $text): void
    {
        header('Content-Type: text/plain');
        echo $text;
        exit;
    }

    /**
     * Réponse XML
     */
    protected function xml(string $xml): void
    {
        header('Content-Type: application/xml');
        echo $xml;
        exit;
    }
}
