<?php
// ============================================================
//  CORE/APP.PHP - Router chính (Front Controller Pattern)
// ============================================================

class App
{

    protected $controller = 'HomeController';
    protected string $method = 'index';
    protected array $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        // Resolve controller
        $controllerFile = APP_PATH . '/controllers/' . ucfirst($url[0] ?? 'home') . 'Controller.php';
        if (file_exists($controllerFile)) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }

        require_once APP_PATH . '/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller();

        // Resolve method
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // Remaining segments as params
        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl(): array
    {
        $request = $_GET['url'] ?? 'home';
        $url = rtrim($request, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return explode('/', $url);
    }
}
