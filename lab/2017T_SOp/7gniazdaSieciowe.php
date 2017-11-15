<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
</head>
<?php
include_once '../../class.geshi.php';
//include_once '../../geshi/functions.geshi.php';
?>
<body>
<h1>Programowanie gniazd sieciowych - obsługa wielu połączeń</h1>
<h2>Wymagania</h2>
<ul>
 <li>Tworzenie i edycja plików</li>
 <li>Obsługa kompilatora gcc</li>
</ul>
<h2>1 Wprowadzenie</h2>
<p>
Celem zajęć jest napisanie programu, który obsługuje wiele połączeń sieciowych. Program taki może dodatkowo wykonywać inne czynności, np. wczytywać dane z klawiatury lub co pewien czas wysyłac powiadomienia. Zastosowania podejścia opisanego na poprzednich zajęciach nie jest wystarczające. Operacje odczytu (również z gniazda sieciowego) jest operacją blokującą. W rezultacie po wywołaniu funkcji do odczytu z gniazda, działanie procesu jest wstrzymane do momentu odebrania wiadomości przez system operacyjny. </p>
<p>
Rozważmy następujący przykład. Serwer obsługuje 2 połączenia: z hostem A i z hostem B. Serwer chce odczytać wiadomość od hosta A, który jeszcze nie wysłał tej wiadomości. Działanie procesu na serwerze jest wstrzymane do momentu, gdy host A wyśle wiadomość. Host A mógł jednak rozłączyć się i nigdy nie wyśle tej wiadomości. Serwer zatem nie obsłuży połączenia z hostem B, ponieważ czeka na wiadomość od hosta A.
</p>
<p>
Przedstawiony problem można rozwiązać na 2 sposoby:
<ol>
 <li>Za pomocą funkcji select sprawdzać, czy na danym gnieździe sieciowym jest wiadomość do odczytu i odczytywać tylko z tych gniazd, dla których system operacyjny przechwycił wiadomość</li>
 <li>Napisać wielowątkowy program</li>
</ol>
<p>
W czasie zajęć oba podejścia zostaną omówione. Jako przykład zostanie przedstawiony program działający jako serwer, który odbiera wiadkomości od klientów i rozsyła je pomiędzy pozostałymi klientami (opcjonalnie można pominąć nadawcę wiadomości).
<p>
Działanie serwera zostanie przetestowane za pomocą programu telnet (lub putty w systemie windows). W przypadku poprawnej implementacji możliwe będzie prowadzenie rozmów.
<p>

<h2>2 Funkcja select</h2>
<p>
Zapoznaj się z działaniem funkcji select oraz makrami FD_ZERO, FD_SET, FD_ISSET. <a href="http://linux.die.net/man/2/select">http://linux.die.net/man/2/select</a> lub w manualu man 
</p>
<p>
Na początku napiszemy program, który będzie działał jak serwer TCP i obsługiwał do 10 połączeń. 
Jego zadaniem będzie nasłuchiwanie połączeń. Jeśli ich liczba nie przekracza 10 to są one przyjmowanie i dodawanie do obsługi. Jeśli program na połączeniu nr X odbierze wiadomosć to rozsyła ją na pozostałe obsługiwane połączenia. 
</p>
<h4>Utworzenie projektu</h4>
<p>
Uruchom program Code::Blocks i utwórz nowy konsolowy projekt (w języku C lub C++). Dodaj do projektu biblioteki:
<?php
$source = '
#include <stdio.h>
#include <arpa/inet.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <string.h>
#include <errno.h>
#include <stdlib.h>
#include <unistd.h>
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<h4>Struktura reprezentująca połączenie</h4>
Zdefiniuj strukturę, która zawiera 2 pola:
<ul>
 <li>pole okreslające stan połączenia (0 brak, 1 zestawione)</li>
 <li>pole okreslające numer deskryptora związanego z tym połączeniem</li>
</ul>
<?php
$source = '
struct polaczenie
{
  int aktywne;
  int fd;
};
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<ul>
 <li>Utwórz 10 elementową tablicę obiektów typu <i>struct polaczenie</i>.</li>
 <li>Zadeklaruj zmienną typu int <b>port</b> pod którą zapisany jest numer portu, na którym serwer nasłuchuje połączenia</li>
 <li>Zadeklaruj strukturę, pod którą zapisane są parametry określających przyjmlowane połączenia: struct sockaddr_in si_local;</li>
 <li>Utwórz obiekt typu <b>struct timeval</b> o nazwie <b>tv</b> pod którym zostanie ustawiony maksymalny czas oczekiwania</li>
 <li>Utwórz tablicę typu char na potrzeby bufora. Tablicę nazwij jako <b>buf</b>. Za pomocą preprocesora zdefiniuj stałą BUFLEN (#define) która określa rozmiar bufora. Zalecana wartość to 1024. </li>
</ul>
Zadeklaruj deskryptor związny z nasłuchiwaniem nowych połączeń.
<?php
$source = '
int fdListen;
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<h4>Przygotowanie programu</h4>
<p>
W metodzie main wyczyść tablicę, która reprezentuje połączenia
<?php
$source = '
  memset(mojePolaczenia, 0, 10*sizeof(struct polaczenie));
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Rozpocznij nasłuchowanie. W tym celu utwórz odpowiednie obiekty, analogicznie jak na poprzednich zajęciach
<?php
$source = '
  if ((fdListen=socket(AF_INET, SOCK_STREAM, 0))==-1)
  {
    perror("socket");
    exit(EXIT_FAILURE);
  }


  si_local.sin_family = AF_INET;
  si_local.sin_port   = htons(port);
  if (bind(fdListen, (const struct sockaddr *)&si_local, sizeof(si_local))==-1)
  {
    perror("bind");
    exit(EXIT_FAILURE);
  }
  listen(fdListen, 10);
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Utwórz obiekt, który reprezentuje deskryptory pod względem gotowości do odczytu danych
<?php
$source = '
  fd_set rfds;
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<h4>Główna pętla programu</h4>
Utwórz główną (nieskończoną) pętlę, w której:
<ol>
 <li>Ustawiany jest stan obiektu reprezentujący deskryptory (makra FD_ZERO i FD_SET)</li>
 <li>Wywołana jest funkcja select</li>
 <li>Na podstawie obiektu reprezentujacego deskryptory i za pomocą makra FD_ISSET sprawdzane są deskryptory pod kątem gotowości do odczytu. Jeśli można odczytać to wykonana jest odpowiednia funkcja</li>
</ol>
</p>
<p>
Ustawianie deskryptorów:
<?php
$source = '
    int maxFd = fdListen;

    FD_ZERO(&rfds);
    FD_SET(fdListen, &rfds);

    for(i=0; i<10; i++)
    {
      if (mojePolaczenia[i].aktywne)
      {
        maxFd = (mojePolaczenia[i].fd > maxFd) ? mojePolaczenia[i].fd : maxFd;
        FD_SET(mojePolaczenia[i].fd, &rfds);
      }
    }    
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Wywołanie funkcji select:
<?php
$source = '
    /* Czekanie nie dłużej niż sekund. */
    tv.tv_sec = 5;
    tv.tv_usec = 0;
    retval = select(maxFd+1, &rfds, NULL, NULL, &tv);
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Obsługa deskryptora nasłuchującego nowych połączeń:
<?php
$source = '
if (FD_ISSET(fdListen, &rfds))
{
  int polIdx = 0;
  for (polIdx=0; polIdx<10; polIdx++)
  {
    if (mojePolaczenia[polIdx].aktywne == 0)
    { //Przyjmowanie nowego połaczenia
      mojePolaczenia[polIdx].aktywne=1;
      mojePolaczenia[polIdx].fd=accept(fdListen, NULL, NULL);
      printf("Nowe polaczenie\n");
      break;
    }
  }
  if (polIdx == 10)
  {  //Serwer obsługuje 10 połączeń, odrzucanie połączenia
     int tmpFd = accept(fdListen, NULL, NULL);
     send(tmpFd, "Odmowa\n", 7, 0);
     close(tmpFd);
  }
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Obsługa deskryptora dla połączenia. Należy wykonać w pętli, gdzie <b>i</b> oznacza numer iteracji.
<?php
$source = '
if (FD_ISSET(mojePolaczenia[i].fd, &rfds))
{
  memset(buf, 0, sizeof(char)*BUFLEN);
  int odczytano = recv(mojePolaczenia[i].fd, buf, BUFLEN, 0);
  if (odczytano < 1)
  { //Zakończenie połączenia
    mojePolaczenia[i].aktywne=0;
    close(mojePolaczenia[i].fd);
    mojePolaczenia[i].fd=-1;
  }
  else
  {
    printf("Odebrałem od klienta %d: %s", i, buf);
    int polIdx;
    for (polIdx=0; polIdx<10; polIdx++)
    { //Rozsyłanie wiadomości do pozostałych połączeń
      if(polIdx == i)
        continue;
       if (mojePolaczenia[polIdx].aktywne)
         send(mojePolaczenia[polIdx].fd, buf, odczytano, 0);
    }
  }
 }
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>

</p>
<h2>3 Program wielowątkowy</h2>
<p>
Zapoznaj się z poniższym kodem, który "działa", ale jest napisany niezgodnie z regułami sztuki.
<?php
$source = '
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <pthread.h>

#define LIMIT 10
#define BUF_LEN 10

struct message
{
  int cliId;
  int length;
  char *msg;
};

struct buffer
{
    int readIdx;
    int writeIdx;

    struct message messages[BUF_LEN];
};

struct clientSession
{
    pthread_t proces;
    int notEmpty;
    int finished;
    int fd;
};

struct clientSession connections[LIMIT];
struct buffer IPCbuffer;
//pthread_mutex_t semaphote = PTHREAD_MUTEX_INITIALIZER;

void *funClSession(void *arg)
{
    char *buf;
    int msgLen;
    int conIdx = *(int *)(arg);

    buf = malloc(1500);
    do
    {
        memset(buf, 0, 1500);
        msgLen = recv(connections[conIdx].fd, buf, 1500, 0);
        IPCbuffer.messages[IPCbuffer.writeIdx].length = msgLen;
        IPCbuffer.messages[IPCbuffer.writeIdx].msg = buf;
        IPCbuffer.messages[IPCbuffer.writeIdx].cliId = conIdx;
        IPCbuffer.writeIdx++;
        if (IPCbuffer.writeIdx == BUF_LEN)
            IPCbuffer.writeIdx = 0;
        //printf("New message received: %s\n", buf);
        //TODO rozesłać do innych połączeń.
        
    }
    while (msgLen > 0);
    free(buf);

    close(connections[conIdx].fd);
    connections[conIdx].finished = 1;
    return NULL;
}

void* funCommunicator(void *arg)
{
    while(1)
    {
        if (IPCbuffer.readIdx != IPCbuffer.writeIdx)
        {
            //printf("Ipc buffer resending\n");
            struct message *msg = &IPCbuffer.messages[IPCbuffer.readIdx];
            IPCbuffer.readIdx++;
            IPCbuffer.readIdx %= BUF_LEN;

            int clId;
            for(clId=0; clId<LIMIT; clId++)
            {
                if (connections[clId].notEmpty == 0)
                    continue;

                if (clId == msg->cliId)
                    continue;
                send(connections[clId].fd, msg->msg, msg->length, 0);
            }
            free(msg->msg);
        }
    }
}

int main()
{
    memset(&IPCbuffer, 0, sizeof(struct buffer));
    memset(connections, 0, sizeof(connections));

    int fdListen;
    int port = 3000;
    struct sockaddr_in si_local;
    if ((fdListen=socket(AF_INET, SOCK_STREAM, 0))==-1)
    {
        perror("socket");
        exit(EXIT_FAILURE);
    }

    si_local.sin_family = AF_INET;
    si_local.sin_port   = htons(port);
    memset(&si_local.sin_addr, 0, sizeof(struct in_addr));
    if (bind(fdListen, (const struct sockaddr *)&si_local, sizeof(si_local))==-1)
    {
        perror("bind");
        exit(EXIT_FAILURE);
    }
    listen(fdListen, 10);


    pthread_t thrCom;
    if (pthread_create(&thrCom, NULL, funCommunicator, NULL) != 0)
    {
        perror("Can\'t create process that is responsible for IPC\n");
        exit(EXIT_FAILURE);
    }

    while(1)
    {
        int newFd=accept(fdListen, NULL, NULL);

        int i;
        for(i=0; i<LIMIT; i++)
        {
            if (connections[i].finished)
            {
                connections[i].finished = 0;
                pthread_join(connections[i].proces, NULL);
                connections[i].notEmpty = 0;
            }

            if (connections[i].notEmpty == 0)
            {
                if (pthread_create(&connections[i].proces, NULL, funClSession,  &i) != 0)
                {
                    perror("Can\'t create new thread");
                    send(newFd, "Can\'t create new thread for connection handle", 48, 0);
                    close(newFd);
                }
                else
                {
                    connections[i].fd = newFd;
                    connections[i].notEmpty = 1;
                }
                break;
            }
        }
        if (i == LIMIT)
        {
            send(newFd, "Too many connections", 20, 0);
            close(newFd);
        }
    }
    return 0;
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h4>Uzupełnij kod</h4>
<ol>
 <li>Dopisz kod, który przekazuje wiadomość na pozostałe połączenia</li>
 <li>Do obsługi połączenia zastosuj 2 wątki. Wątek pierwszy (odpowiednik producenta) odbiera dane z gniazda i wysyła je do bufora. Wątek drugi odbiera dane z bufora i rozsyła ja na pozostałe połączenia</li>
</ol>

<h2>5 Bibliografia</h2>
<ul>
 <li><a href="http://www.linuxpl.org/LPG/node81.html">http://www.linuxpl.org/LPG/node81.html</a></li>
 <li><a href="http://beej.us/guide/bgnet/">http://beej.us/guide/bgnet/</a></li>
 <li><a href="http://linux.die.net/man/3/htonl">http://linux.die.net/man/3/htonl</a></li>
 <li><a href="http://linux.die.net/man/2/send">http://linux.die.net/man/2/send</a></li>
 <li><a href="http://linux.die.net/man/2/recv">http://linux.die.net/man/2/recv</a></li>
</ul>


