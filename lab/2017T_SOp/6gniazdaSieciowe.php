<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
</head>
<?php
include_once '../../class.geshi.php';
//include_once '../../geshi/functions.geshi.php';
?>
<body>
<h1>Komunikacja międzyprocesowa - programowanie gniazd sieciowych</h1>
<h2>Wymagania</h2>
<ul>
 <li>Tworzenie i edycja plików</li>
 <li>Obsługa kompilatora gcc</li>
</ul>
<h2>1 Wprowadzenie</h2>
<p>
Gniazdka sieciowe (ang. <i>sockets</i>) są najczęściej stosowanym mechanizmem do obsługi komunikacji międzyprocesowej IPC (ang. <i>Inter Process Communication</i>). 
Umożliwiają one komunikację pomiędzy procesami działającymi na tej samej maszynie jak również komunikację pomiędzy różnymi komputerami.
Implementacja gniazd sieciowych w systemie Linux wzorowana jest na kodzie pochodzącym z systemu BSD (<i>Berkeley System Distribution</i>) w wersji 4.4. 
W innych systemach operacyjnych, np. systemie Windows, wzorowano się na tym samym rozwiązaniu, dzięki czemu obsługa sieci jest podobna w wielu systemach operacyjnych.
Jeśli dwa procesy mają się między sobą komunikować, każdy z nich tworzy po swojej stronie jedno gniazdo (ang <i>socket</i>).
Parę takich gniazd można więc określić mianem końcówek kanału komunikacyjnego. Gniazd używa się głównie do komunikacji z odległym procesem za pośrednictwem sieci, 
jednak można je zastosować także w przypadku wymiany informacji między procesami działającymi w obrębie jednej maszyny. 
Ta uniwersalność zastosowań jest zapewniona dzięki istnieniu różnych odmian gniazd. Gniazdo jest opisywane za pomocą kombinacji trzech atrybutów: 
<ol>
 <li>Domeny adresowej</li>
 <li>Sposobu komunikacji</li>
 <li>Protokołu sieciowego</li>
</ol>
</p>
<p>
W dalszej części zajęć zajmiemy się komunikacją za pomocą protokołu TCP. 
Jest to model komunikacji typu klient serwer. Klient nawiązuje połączenie. 
Serwer natomiast nasłuchuje połączeń. W celu nawiązania połączenia TCP należy podać adres IP serwera. 
W protokole TCP (podobnie jak w protokole UDP) wprowadzono dodatkowo dwa 16 bitowe pola: adres źródłowy i adres docelowy. 
Pola takie umożliwiają na identyfikację połączeń, dzięki czemu pomiędzy klientem, a serwerem może być zestawione wiele równoległych połączeń. 
</p>
<h2>2 Serwer TCP</h2>
<p>
Na początku napiszemy program, który będzie działał jak serwer TCP. 
Jego zadaniem będzie nasłuchiwanie połączeń i wypisywanie na ekranie odebranych komunikatów. 
Następnie serwer zostanie zmodyfikowany o obsługę polecenia <b>exit</b>, po odebraniu którego serwer zamyka połączenie z klientem.
</p>
<p>
Instrukcja została celowo tak przygotowana, by rozpoczać od serwera. Umożliwi nam to przetestowanie napisanego programu, ponieważ jako klienta można wykorzystać program telnet.
</p>
<h3>Wymagane biblioteki</h3>
<p>
<?php
$source = '
#include <arpa/inet.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <string.h>'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
W celu napisania serwera TCP należy:
<ol>
 <li>Utworyzć gniazdo sieciowe</li>
 <li>Przypisać do gniazda sieciowego adres</li>
 <li>Rozpocząć nasłuchiwanie</li>
 <li>Zaakceptować połączenie (zostanie utworzone nowe gniazdo)</li>
 <li>Obsługiwać komunikację na nowo utworzonym gnieździe z klientem, który się połączył</li>
</ol>
</p>
<h3>2.1 Tworzenie gniazda</h3>
<p>
Do tworzenia gniazd służy funkcja <i>socket(int domain, int type, int protocol)</i>, która przyjmuje 3 parametry:
<ol>
 <li><b>domain</b> - Tzw. domena adresowa, oznacza domenę w której nastąpi komunikacja poprzez to gniazdo. Jest to konieczne aby określić, jaki rodzaj adresu będziemy przypisany do gniazda. Na potrzeby dzisiejszych zajeć zostanie podana wartość PF_INET.
  parametr domain może mieć następujące wartości:
  <ul>
   <li>PF_LOCAL (PF_UNIX) - komunikacja w obrębie jednej maszyny</li>
   <li>PF_INET - Internet, czyli używamy protokołów z rodziny TCP/IP PF_IPX - protokoły IPX/SPX (Novell)</li>
   <li>PF_PACKET - niskopoziomowy interfejs do odbierania pakietów w tzw. surowej (ang. <i>raw</i>) postaci</li>
  </ul>
  Wszystkie dopuszczalne wartości znajdują się w pliku bits/socket.h. Zaglądając tam spostrzeżemy, że zamiennie używa się notacji PF_xxx (ang. PF - Protocol Family) oraz AF_xxx (ang. AF - Address Family). W niektórych kodach zamiast AF_INET może byc PF_INET.
 </li>
 <li><b>type</b> - Sposob komunikacji. Na potrzeby zajeć zostanie wybrana wartość SOCK_STREAM. Możliwe wartości to:
  <ul>
   <li>SOCK_STREAM - dwukierunkowa komunikacja zorientowana na połączenia </li>
   <li>SOCK_DGRAM - komunikacja bezpołączeniowa korzystająca z tzw. datagramów (np. dla protokołu UDP)</li>
   <li>SOCK_RAW - dostęp do surowych pakietów na poziomie warstwy sieciowej modelu TCP/IP</li>
   <li>SOCK_PACKET - ten typ był używany do obsługi surowych pakietów ale z warstwy fizycznej (ang. link layer), czyli o jedną warstwę "niżej" od SOCK_RAW; obecnie nie należy stosować tego typu - jego rolę przejęła oddzielna domena PF_PACKET</li>
  </ul>
 </li>
  Najczęściej używanymi typami są trzy pierwsze. Wszystkie wartości są zdefiniowane w pliku bits/socket.h.
 </li>
 <li><b>protocol</b> - Określa konkretny protokół, którego będziemy używać. Zazwyczaj w obrębie jednej domeny adresowej i sposobu komunikacji istnieje tylko jeden protokół do ich obsługi. Na przykład, jeśli korzystamy z domeny PF_INET oraz SOCK_STREAM to nie mamy dużego wyboru ponieważ możemy skorzystać tylko z protokołu TCP. W takim wypadku parametrowi protocol nadajemy wartość 0 - system sam określi, jakiego protokołu używać. Czasami jednak zachodzi możliwość wyboru spośród kilku różnych protokołów. Rozważmy przykład gniazda PF_INET ale typu SOCK_RAW. W tym przypadku dzięki parametrowi protocol możemy określić, jakie konkretnie pakiety nas interesują. Wszystkie dopuszczalne wartości dla domeny PF_INET znajdziemy w linux/in.h. 
</ol>
Funkcja socket zwraca numer deskryptora, reprezentujący gniazdo. W systemie Linux w podobny sposób (za pomocą deskryptorów) reprezentowane są otwarte pliki. Każdy proces ma lokalna tablicę deskryptorów. Ponadto dla wszystkich procesów istnieje globalna tablica deskryptorów.
</p>
<?php
$source = '
  void main()
  {
    int gnNasluchujace = socket(PF_INET,SOCK_STREAM,0);
    //często zmienna ma angielską nazwę, np. socketfd
  }
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>
Zauważmy, że utworzone gniazdo, nie zawiera adresu drugiej strony. W przypadku serwera należy utworzyć gniazdo bierne, ponieważ klient nawiązuje połączenie. Zauważmy jednak, że nie został określony port, na którym serwer nasłuchuje. Do określenia portu oraz grupy adresów jakie mogą łączyć się z serwerem służy wiązanie adresów.
</p>
<h3>2.2 Wiązanie adresu z gniazdem</h3>
<p>
Do przypisania adresu dla gniazda służy funkcja bind (więcej informacji po wpisaniu polecenia <i>man 2 bind</i>).
</p>
<p>
Funkcja <i>int bind(int sockfd, struct sockaddr *my_addr, int addrlen)</i> przyjmuje następujące parametry:
<ol>
 <li>Identyfikator deskryptora gniazda (wartość zapisana pod zmienną gnNasluchujace).</li>
 <li>Wskaźnik do struktury zawierająca adres, należy ją wcześniej utworzyć strukturę odpowiedniego typy (typ struktury zależy od protokołu) i rzutować na typ struct my_addr*.</li>
 <li>Długość struktury zawierającej adres i gniazdo. W zależności od protokołu struktura może mieć różną długość.</li>
</ol> 
</p>
<p>
Pojawia się pytanie. Po co rzutować, czy nie prościej ręcznie utworzyć i wypełnić strukturę sockaddr Struktura ta ma następującą postać:
<?php
$source = '
struct sockaddr
{
    sa_family_t sa_family;
    char        sa_data[14];
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
By wypełnić strukturę należy znać znaczenie każdego z bajtów w tablicy sa_data. Łatwiej jest zastosować strukturę struct sockaddr_in. By używać tej struktury należy dołączyć bibliotekę <b>netinet/in.h</b>. Sama struktura zawiera następujące pola
<?php
$source = '
struct sockaddr_in
{
    short            sin_family;   // e.g. AF_INET, AF_INET6
    unsigned short   sin_port;     // e.g. htons(3490)
    struct in_addr   sin_addr;     // see struct in_addr, below
    char             sin_zero[8];  // zero this if you want to
}
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
Poniższy kod przedstawia sposób wypełnienia struktury
<?php
$source = '
struct sockaddr_in adresGniazda;
//Wypełnianie zerami struktury
bzero(&adresGniazda, sizeof(struct sockaddr_in));

adresGniazda.sin_family = AF_INET;
adresGniazda.sin_port   = htons(2000);
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
Adres został tak skonfigurowany, by serwer nasłuchiwał na porcie 2000. 
Konieczna jest zamiana liczby 2000 na porządek sieciowy, stąd też funkcje htons.
Więcej o konwersji na porządek sieciowy i hosta można przeczytać na: <a href="http://linux.die.net/man/3/htonl">http://linux.die.net/man/3/htonl</a> lub w manualu (man htons).
</p>
<p>
Gdy zostanie utworzona struktura zawierająca adres, można jej użyć, by do gniazda przypisać port, na których serwer nasłuchuje.
<?php
$source = '
bind(gnNasluchujace, (struct sockaddr*)(&adresGniazda), sizeof(struct sockaddr_in));
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<h3>2.3 Rozpoczęcie nasłuchiwania</h3>
<p>
w celu rozpoczęcia nasłuchiwania należy wywołać funkcję listen. Funkcja <i>int listen(int deskryptorGniazda, int liczbaOczekPol)</i> przyjmuje następujące argumenty:
<ol>
 <li>deskryptorGniazda - identyfikator deskryptora gniazda na którym serwer nasłuchuje - w naszym przypadku wartość zmiennej gnNasluchujace</li>
 <li>liczbaOczekPol - maksymalna długość kolejki z oczekującymi połączeniami</li>
</ol>
Funkcja listen jest funkcją blokującą. Wykonywanie programu zostanie zatrzymane do momentu odebrania nowego połączenia. Jeśli program wykonuje więcej czynności to należy napisać go na wielu wątkach lub też zastosować funkcję select. Teraz zostanie zastosowane najprostsze rozwiązanie. Program zawiesi swoje działanie do momentu odebrania połączenia.


</p>
<h3>2.4 Przyjmowanie nowego połączenia</h3>
<p>
Do przyjmowania połączeń służy funkcja accept: <i>int accept(int gniazdo, struct sockaddr *addr, socklen_t *addrlen);</i>
Funkcja ta zwraca identyfikator dla nowo utworzonego gniazda odpowiedzialnego za komunikację z nowym klientem. Jeśli zwróci wartość -1 to oznacza to, że nie udało się nawiązać połączenia.
Funkcja przyjmuje następujące argumenty:
<ol>
 <li>deskryptorGniazda - identyfikator deskryptora gniazda na którym serwer nasłuchuje - w naszym przypadku wartość zmiennej gnNasluchujace</li>
 <li>wskaźnik do struktury, pod którą zostanie zapisany adres klienta, jaki nawiązał połączenie, można podać wartość NULL lub utworzyć obiekt struktury sockaddr_in i jego referencję rzutować na struct sockaddr</li>
 <li>wskaźnik do typu socklen_t, pod który zostanie zapisana długość adresu, można podać NULL</li>
</ol>
</p>
<p>
Przykład - najprostszy wariant:
<?php
$source = '
int gnPolZ_klientem = accept(gnNasluchujace, NULL, NULL)
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
Przykład - odczytywanie adresu IP klienta:
<?php
$source = '
  struct sockaddr_in adresklienta;
  bzero(&adresklienta, sizeof(struct sockaddr_in));
  socklen_t dlAdresu = sizeof(struct sockaddr_in);
  int gnPolZ_klientem = accept(gnNasluchujace,(struct sockaddr*)( &adresklienta), &dlAdresu);
  char adresStr[INET_ADDRSTRLEN];
  inet_ntop(AF_INET, &adresklienta.sin_addr, adresStr, INET_ADDRSTRLEN);
  printf("Nawiązano połączenie z %s", adresStr);
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h3>2.5 Wysyłanie i odbieranie wiadomości</h3>
<p>
Wiadomości można wysyłać na wiele sposobów, nawet za pomocą tych samych funkcji, które służą do zapisu w pliku. Do obsługi gniazd została napisana specjalna funkcja send <a href="http://linux.die.net/man/2/send">http://linux.die.net/man/2/send</a>.
Funkcja ssize_t send(int sockfd, const void *buf, size_t len, int flags) zwraca liczbę wysłanych bajtów. Przyjmuje następujące argumenty:
<ol>
 <li>int sockfd - identyfikator deskryptora gniazda - w naszym praypadku wartość zmiennej gnPolZ_klientem</li>
 <li>const void *buf - wskaźnik do danych, jakie chcemy przesłać. Należy wcześniej utworzyć odpowiednią strukturę/tablicę</li>
 <li>size_t len - długość (w bajtach) struktury, jaką chcemy przesłać</li>
 <li>int flags - flagi, można wpisać wartość 0</li>
</ol>
</p>
<p>
Odbieranie wiadomości jest analogiczne. Służy do tego funkcja <i>ssize_t recvmsg(int sockfd, struct msghdr *msg, int flags);</i>  Neleży wcześniej utwtorzyć tablicę, pod którą będzie zapisana wiadomość. Więcej informacji na stronie <a href="http://linux.die.net/man/2/recv">http://linux.die.net/man/2/recv</a>
</p>
<h2>3 Zadania do samodzielnego wykonania</h2>
<h3>Zadanie 1</h3>
Napisz program z serwerem TCP. Serwer nasłuchuje połączeń na porcie 3000. Po odebraniu połączenia serwer wypisuje na ekranie stosowną informację oraz zamyka połączenie. Następnie zamyka gniazdo nasłuchujące i kończy pracę. Do zamykania deskryptorów służy funkcja close.

<h3>Zadanie 2</h3>
Poprzedni program poszerz o wypisywanie wysyłanie do klienta wiadomości z napisem "Serwer kończy prace".

<h3>Zadanie 3</h3>
Napisz program z serwerem TCP. Serwer odbiera wiadomości od klienta i ich treść wysyła na ekranie.

<h3>Zadanie 4</h3>
Napisz program z serwerem TCP. Serwer odbiera wiadomości od klienta i ich treść wysyła na ekranie. Po odebraniu wiadomości z napisem "exit" serwer zamyka połączenie i kończy pracę.

<h3>Zadanie 5 tylko dla orłów</h3>
<p>
Napisz program z serwerem TCP. Serwer odbiera wiadomości od klienta i ich treść wyświetla na ekranie. Serwer odczytuje ze swojego strumienia wejścia i odebrane znaki wysyła do klienta.
</p>
<p>
Wskazówka. Poniżej zamieszczono kod programu, który zczytuje znaki z klawiatury i wysyła je za pomocą protokołu UDP. Ponadto program odbiera wiadomości UDP i wysyła je na ekran.
</p>
<p>
Plik nagłówkowy <b>vt100.h</b>:
<?php
$source = '
#include <string.h>

int strToVt100(const char *scrBuf, int srcBufLen, char *dstBuf, int maxDstBufLen);
int vt100Tostr(const char *scrBuf, int srcBufLen, char *dstBuf, int maxDstBufLen);
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Plik z kodem <b>vt100.c</b>:
<?php
$source = '
#include "vt100.h"

// defines
#define ASCII_BEL           0x07
#define ASCII_BS            0x08
#define ASCII_CR            0x0D
#define ASCII_LF            0x0A
#define ASCII_ESC           0x1B
#define ASCII_DEL           0x7F

#define VT100_ARROWUP       \'A\'
#define VT100_ARROWDOWN     \'B\'
#define VT100_ARROWRIGHT    \'C\'
#define VT100_ARROWLEFT     \'D\'
#define VT100_ATTR_OFF      0
#define VT100_BOLD          1
#define VT100_USCORE        4
#define VT100_BLINK         5
#define VT100_REVERSE       7
#define VT100_BOLD_OFF      21
#define VT100_USCORE_OFF    24
#define VT100_BLINK_OFF     25
#define VT100_REVERSE_OFF   27


int strToVt100(const char *srcBuf, int srcBufLen, char *dstBuf, int maxDstBufLen)
{
  int result = 0;
  int i;
  for (i=0; i < srcBufLen; i++)
  {
    switch (*srcBuf)
    {
      case ASCII_LF:
      case ASCII_CR:
//        *dstBuf++ = ASCII_ESC;
//        *dstBuf++ = \'[\';
        *dstBuf++ = ASCII_CR;
//        result +=3;
        result ++;
        break;
      case ASCII_DEL:
//        *dstBuf++ = ASCII_ESC;
//        *dstBuf++ = \'[\';
        *dstBuf++ = ASCII_BS;
//        result +=3;
        result ++;
        break;
      default:
        *dstBuf++ = *srcBuf;
        result++;
        break;
    }
    srcBuf++;
  }
  return result;  
}

int vt100Tostr(const char *scrBuf, int srcBufLen, char *dstBuf, int maxDstBufLen)
{
  strncpy(dstBuf, scrBuf, srcBufLen);
  return srcBufLen;  
}
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<p>
Plik nagłówkowy <b>udpconsole.h</b>:
<?php
$source = '
#include <stdio.h>
#include <stdlib.h>
#include <termios.h>
#include <errno.h>
#include <unistd.h>
#include <string.h>
#include <sys/time.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <netinet/in.h>

#include "vt100.h"

#define BUFLEN 1024

struct sockaddr_in   si_local;
struct sockaddr_in   si_remote;
int                  fd;
int                  local_port;
int                  remote_port;
char                 buf[BUFLEN];
char                 buf2[BUFLEN];
char                 srv_addr[16];

/*void wait_and_exit(void);*/
void term_error(void);
int flush_term(int term_fd, struct termios *p);
/*int keypress(int term_fd);*/
int checktty(struct termios *p, int term_fd);
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>

</p>
<p>
Plik z kodem <b>udpconsole.c</b>:
<?php
$source = '
#include "udpconsole.h"
#include "vt100.h"

int main(int argc, char **argv)
{
  if(argc!=4)
  {
    fprintf(stderr, "Usage: %s <ip> <local port number> <remote port number>\n", argv[0]);
    exit(EXIT_FAILURE);
  }

  memset(srv_addr, 0, 16);
  memcpy(srv_addr, argv[1], strlen(argv[1]));
  
  local_port=atoi(argv[2]);
  remote_port=atoi(argv[3]);

  if((local_port < 1024) || (remote_port < 1024))
  {
    fprintf(stderr, "Usage: %s <ip> <local port number> <remote port number>\n", argv[0]);
    fprintf(stderr, "\twhere <port number> shall be > 1023\n");
    exit(EXIT_FAILURE);
  }

  struct termios  attr;
  struct termios *p=&attr;
  int fd_term_in=fileno(stdin);
  int fd_term_out=fileno(stdout);

  if(!flush_term(fd_term_in, p) )
    term_error();
  
  if ((fd=socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP))==-1)
  {
    perror("socket");
    exit(EXIT_FAILURE);
  }

  memset((char *) &si_local, 0, sizeof(si_local));
  si_local.sin_family       =  AF_INET;
  si_local.sin_port         =  htons(local_port);
  si_local.sin_addr.s_addr  =  htonl(INADDR_ANY);

  memset((char *) &si_remote, 0, sizeof(si_local));
  si_remote.sin_family       =  AF_INET;
  si_remote.sin_port         =  htons(remote_port);
  if (inet_aton(srv_addr, &si_remote.sin_addr)==0)
  {
    fprintf(stderr, "inet_aton() failed\n");
    exit(EXIT_FAILURE);
  }

  
  if (bind(fd, (const struct sockaddr *)&si_local, sizeof(si_local))==-1)
  {
    printf("Failed to bind ip %s\r\n", inet_ntoa(si_local.sin_addr));
    perror("bind");
    exit(EXIT_FAILURE);
  }

  fd_set rfds;
  struct timeval tv;
  int retval;

/* Obserwacja stdin (fd 0) i sprawdzanie kiedy ma wejście. */
  FD_ZERO(&rfds);
  FD_SET(0, &rfds);
  FD_SET(fd, &rfds);


  while(1)
  {
    FD_ZERO(&rfds);
    FD_SET(0, &rfds);
    FD_SET(fd, &rfds);
    /* Czekanie nie dłużej niż sekund. */
    tv.tv_sec = 5;
    tv.tv_usec = 0;
    retval = select(fd+1, &rfds, NULL, NULL, &tv);
    /* Nie należy już polegać na wartości tv! */

    if (retval)
    {
      if (FD_ISSET(0, &rfds))
      {
        int odczytano = read(0, buf, BUFLEN);
        if (odczytano == -1)
        {
          perror("read");
          exit(EXIT_FAILURE);	  
        }
        odczytano = strToVt100(buf, odczytano, buf2, BUFLEN);
        if (sendto(fd, buf2, odczytano, 0, (struct sockaddr *)&si_remote, sizeof(si_remote))==-1)
        {
          perror("send");
          exit(EXIT_FAILURE);
        }
      }
      if (FD_ISSET(fd, &rfds))
      {
        memset(buf, 0, sizeof(char)*BUFLEN);
        int odczytano = recv(fd, buf, BUFLEN, 0);
        if (odczytano == -1)
        {
          perror("recvfrom()");
          exit(EXIT_FAILURE);
        }
        if (write(1, buf, odczytano) == -1)
        {
          perror("write to console");  
        }
      }
    }
  }
  close(fd);
  return 0;
}

int checktty(struct termios *p, int term_fd)
{
  struct termios ck;
return (tcgetattr(term_fd, &ck) == 0 && (p->c_lflag == ck.c_lflag) && (p->c_cc[VMIN] == ck.c_cc[VMIN]) && (p->c_cc[VTIME] == ck.c_cc[VMIN]) );
}

int flush_term(int term_fd, struct termios *p)
{
  struct termios newterm;
  errno=0;
  tcgetattr(term_fd, p);  /* get current stty settings*/

  newterm = *p; 
//  newterm.c_lflag &= ~(ECHO); 
  newterm.c_lflag &= ~(ECHO | ICANON); 
  newterm.c_cc[VMIN] = 0; 
  newterm.c_cc[VTIME] = 0; 

  return(tcgetattr(term_fd, p) == 0 && tcsetattr(term_fd, TCSAFLUSH, &newterm) == 0 && checktty(&newterm, term_fd) != 0);
}

void term_error(void)
{
  fprintf(stderr, "unable to set terminal characteristics\n");
  perror("");                                                
  exit(1);                                                   
}
';
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>

<h2>4 Co dalej</h2>
<p>
Przedstawione programy są bardzo proste. Brak w nich możliwości obsługi wielu połączeń. Co więcej brak możliwości jednoczesnego odczytu danych z kilku strumieni. Operacje odczytu są bowiem operacjami blokującymi. Problem operacji blokujących można rozwiązać na 2 sposoby:
<ol>
 <li>Funkcja select, która wskazuje na deskryptor, który odebrał dane (zadanie 6)</li>
 <li>Zastosowanie wątków (wielowątkowy serwer)</li>
</ol>
</p>

<h2>5 Bibliografia</h2>
<ul>
 <li><a href="http://www.linuxpl.org/LPG/node81.html">http://www.linuxpl.org/LPG/node81.html</a></li>
 <li><a href="http://beej.us/guide/bgnet/">http://beej.us/guide/bgnet/</a></li>
 <li><a href="http://linux.die.net/man/3/htonl">http://linux.die.net/man/3/htonl</a></li>
 <li><a href="http://linux.die.net/man/2/send">http://linux.die.net/man/2/send</a></li>
 <li><a href="http://linux.die.net/man/2/recv">http://linux.die.net/man/2/recv</a></li>
</ul>


