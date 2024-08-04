<?php

// Conexão com o banco de dados
$servidor = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'bot_curso';
$conn = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conn) {
    die("Falha na conexão com o banco de dados");
}

// Variáveis importantes
$numero_telefone = $_GET['telefone'];
$msg = $_GET['msg'];
$usuario = $_GET['usuario'];

// Função para identificar a pergunta e responder adequadamente
function responder_mensagem($mensagem) {
    $mensagem = strtolower($mensagem);

    // Respostas padrão
    $respostas = [
        "oi" => "Olá! Como posso ajudá-lo hoje?",
        "como você está" => "Estou bem, obrigado por perguntar! Como posso ajudá-lo?",
        "o que deseja" => "Estou aqui para ajudar. Por favor, me diga como posso assisti-lo.",
        "irei te direcionar" => "Ok, estou direcionando você para o setor adequado.",
        "irei anotar seu recado" => "Certo, vou anotar seu recado.",
        "ele entrará em contato em breve" => "Entendido. Ele entrará em contato com você em breve."
    ];

    // Verificar se a mensagem corresponde a alguma das perguntas básicas
    foreach ($respostas as $pergunta => $resposta) {
        if (strpos($mensagem, $pergunta) !== false) {
            return $resposta;
        }
    }

    // Resposta padrão caso a pergunta não seja reconhecida
    return "Desculpe, não entendi sua pergunta. Por favor, poderia reformular?";
}

// Consultar o status do usuário
$sql = "SELECT * FROM usuario WHERE telefone = '$numero_telefone'";
$query = mysqli_query($conn, $sql);
$total = mysqli_num_rows($query);

$status = 0;
if ($total > 0) {
    $rows_usuarios = mysqli_fetch_array($query);
    $status = $rows_usuarios['status'];
}

// Menus de respostas padrão
$menus = [
    1 => 'Não sei quanto você ganha, mas a oportunidade que hoje estou oferecendo é salário de 2 mil a 8 mil por mês trabalhando de um celular ou computador, inicialmente trabalhando apenas meio período, o serviço de Marketing online, você já trabalhou com isso?',
    2 => 'Ainda tenho três vagas na equipe de vendas online, te interessa saber mais?',
    3 => 'Dá uma olhada nesse link https://editacodigo.com.br/index/curso.php',
    4 => 'Então como tinha te falado, o link https://editacodigo.com.br/index/curso.php responde a todas as suas dúvidas.'
];

// Verificar se o usuário é novo
if ($total == 0) {
    $sql = "INSERT INTO usuario (telefone, status) VALUES ('$numero_telefone', '1')";
    $query = mysqli_query($conn, $sql);
    if ($query) {
        $resposta = "Olá, me chamo Victor, e trabalho com internet. Hoje estou recrutando pessoas para trabalharem de sua própria casa. Gostaria de te explicar como funciona, ok?";
    }
} else {
    // Usuário existente: obter resposta padrão
    $resposta = responder_mensagem($msg);
    if ($status > 0 && $status < 5) {
        $resposta = $menus[$status];
        $status++;
        $sql = "UPDATE usuario SET status = '$status' WHERE telefone = '$numero_telefone'";
        $query = mysqli_query($conn, $sql);
    } else {
        $resposta = 'Muito obrigado pela sua atenção';
        $sql = "UPDATE usuario SET status = '1' WHERE telefone = '$numero_telefone'";
        $query = mysqli_query($conn, $sql);
    }
}

// Exibir a resposta
echo $resposta;

// Registrar histórico
$data = date('d-m-Y');
$sql = "INSERT INTO historico (telefone, msg_cliente, msg_bot, data) VALUES ('$numero_telefone', '$msg', '$resposta', '$data')";
$query = mysqli_query($conn, $sql);

?>
