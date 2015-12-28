<?php
    header('Content-Type: text/plain;'); //Мы будем выводить простой текст
    set_time_limit(0); //Скрипт должен работать постоянно
    ob_implicit_flush(); //Все echo должны сразу же отправляться клиенту
    $address = 'localhost'; //Адрес работы сервера
    $port = 10002; //Порт работы сервера (лучше какой-нибудь редкоиспользуемый)

    // Определение MAC посетителя по его IP
    function getMAC($ip) {
        $matches=array();
    	$command = "arping -c 3 ".$ip;
        exec($command, $rgResult); // Необходимо в sudoers прописать права для пользователя apache на запуск команды arping
        $mac_template="/[\d|A-F]{2}\:[\d|A-F]{2}\:[\d|A-F]{2}\:[\d|A-F]{2}\:[\d|A-F]{2}\:[\d|A-F]{2}/i";
	preg_match($mac_template, $rgResult[1], $matches);
        return $matches[0];
    }


    if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
        //AF_INET - семейство протоколов
        //SOCK_STREAM - тип сокета
        //SOL_TCP - протокол
        echo "Ошибка создания сокета";
    }
    else {
        echo "Сокет создан\n";
    }
    //Связываем дескриптор сокета с указанным адресом и портом
    if (($ret = socket_bind($sock, $address, $port)) < 0) {
	echo "Ошибка связи сокета с адресом и портом";
    }
    else {
	echo "Сокет успешно связан с адресом и портом\n";
    }
    //Начинаем прослушивание сокета (максимум 5 одновременных соединений)
    if (($ret = socket_listen($sock, 5)) < 0) {
        echo "Ошибка при попытке прослушивания сокета";
    }
    else {
        echo "Ждём подключение клиента\n";
    }
    do {
        //Принимаем соединение с сокетом
        if (($msgsock = socket_accept($sock)) < 0) {
            echo "Ошибка при старте соединений с сокетом";
        } else {
            echo "Сокет готов к приёму сообщений\n";
        }
        //$msg = "Hello!"; //Сообщение клиенту
        //echo "Сообщение от сервера: $msg";
        //socket_write($msgsock, $msg, strlen($msg)); //Запись в сокет
        //Бесконечный цикл ожидания клиентов
        //do {
    	    //$buf = socket_read($msgsock, 1024);
    	    //echo 'Сообщение от клиента: ';
            if (false === ($buf = socket_read($msgsock, 1024))) {
                echo "Ошибка при чтении сообщения от клиента";       }
            //else {
                //    echo $buf."\n"; //Сообщение от клиента
            //}
            //Если клиент передал exit, то отключаем соединение
            /*if ($buf == 'exit') {
                socket_close($msgsock);
                break 2;
            }*/
            if ($buf<>"") {
        	$msg = getMAC($buf);
        	if ($msg=="") {
        	    $msg="00:00:00:00:00:00";
        	} 
        	echo "Запрос MAC для IP=".$buf." MAC=".$msg."\n";
        	socket_write($msgsock, $msg, strlen($msg));
            }
        //} while (true);
    } while (true);
    //Останавливаем работу с сокетом
    if (isset($sock)) {
        socket_close($sock);
        echo "Сокет успешно закрыт";
    }
 ?>