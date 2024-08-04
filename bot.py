from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
import os
import time
import requests

# Configuração da API
API_URL = "https://editacodigo.com.br/index/api-whatsapp/xgLNUFtZsAbhZZaxkRh5ofM6Z0YIXwwv"
HEADERS = {"User-Agent": 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36'}

# Configurações do Chrome
dir_path = os.getcwd()
chrome_options = Options()
chrome_options.add_argument(f"user-data-dir={dir_path}/pasta/sessao")
driver = webdriver.Chrome(options=chrome_options)
driver.get('https://web.whatsapp.com/')
time.sleep(10)

def obter_elementos_api():
    response = requests.get(API_URL, headers=HEADERS)
    time.sleep(1)
    api = response.text.split(".n.")
    elementos = {
        "bolinha_notificacao": api[3].strip(),
        "contato_cliente": api[4].strip(),
        "caixa_msg": api[5].strip(),
        "msg_cliente": api[6].strip(),
        "caixa_msg2": api[7].strip(),
        "caixa_pesquisa": api[8].strip()
    }
    return elementos

def enviar_mensagem(telefone, mensagem):
    try:
        response = requests.get(f"{API_URL}?telefone={telefone}&msg={mensagem}")
        return response.text.strip()
    except Exception as e:
        print(f"Erro ao enviar mensagem: {e}")
        return "Desculpe, ocorreu um erro ao processar sua mensagem."

def bot(elementos):
    try:
        # Capturar a bolinha de notificação
        bolinhas = driver.find_elements(By.CLASS_NAME, elementos["bolinha_notificacao"])
        if bolinhas:
            bolinha = bolinhas[-1]
            acao_bolinha = webdriver.common.action_chains.ActionChains(driver)
            acao_bolinha.move_to_element_with_offset(bolinha, 0, -20).click().perform()
            time.sleep(1)

            # Pegar o telefone do cliente
            telefone_cliente = driver.find_element(By.XPATH, elementos["contato_cliente"])
            telefone_final = telefone_cliente.text
            print(telefone_final)
            time.sleep(2)

            # Pegar a mensagem do cliente
            todas_as_msg = driver.find_elements(By.CLASS_NAME, elementos["msg_cliente"])
            msg_cliente = todas_as_msg[-1].text
            print(msg_cliente)
            time.sleep(2)

            # Obter resposta do servidor PHP
            resposta = enviar_mensagem(telefone_final, msg_cliente)

            # Responder ao cliente
            campo_de_texto = driver.find_element(By.XPATH, elementos["caixa_msg"])
            campo_de_texto.click()
            time.sleep(1)
            campo_de_texto.send_keys(resposta, Keys.ENTER)

            # Fechar o contato
            webdriver.ActionChains(driver).send_keys(Keys.ESCAPE).perform()
        else:
            print('Nenhuma nova mensagem.')
    except Exception as e:
        print(f'Erro: {e}')

if __name__ == "__main__":
    elementos_api = obter_elementos_api()
    while True:
        bot(elementos_api)
        time.sleep(5)
