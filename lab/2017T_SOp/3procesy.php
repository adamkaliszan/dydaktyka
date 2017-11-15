<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
</head>
<?php
include_once '../../class.geshi.php';
?>
<body>
<h1>Programowanie w C</h1>
<p>
W trakcie zajęć zostaną napisane programy w języku C. Zostanie przedstawiony sposób obsługi procesów.
</p>
<h1>Teoria</h1>
<h2>Obsługa procesów w systemie Linux</h2>
<p>
System Linux udostępnia mechanizm tworzenia nowych procesów, usuwania procesów oraz uruchamiania programów. Każdy proces, z wyjątkiem procesu systemowego o identyfikatorze 1, tworzony jest przez inny proces, który staje się jego przodkiem zwanym też procesem rodzicielskim (ang. <i>parent</i>) lub macierzystym, lub krótko rodzicem. 
Nowo utworzony proces nazywany jest potomkiem (ang. <i>child</i>) lub procesem potomnym. Procesy w systemie Linux tworzą zatem drzewiastą strukturę
hierarchiczną, podobnie jak katalogi.
</p>
<p>
Drzewiastą strukturę można zobaczyć po wpisaniu polecenia <i>pstree</i>.
</p>
<p>
Potomek tworzony jest w wyniku wywołania przez przodka funkcji systemowej <b>fork</b>. 
Po utworzeniu potomek kontynuuje wykonywanie programu swojego przodka od miejsca wywołania
funkcji fork. Proces może się zakończyć dwojako: w sposób naturalny przez wywołanie funkcji systemowej <b>exit</b> lub w wyniku reakcji na sygnał. 
Funkcja systemowa exit wywoływana jest niejawnie na końcu wykonywania programu przez proces lub może być wywołana jawnie w każdym innym miejscu programu. Zakończenie procesu w wyniku otrzymania sygnału nazywane jest zabiciem.
</p>
<p>
Do procesu można wysłac sygnał za pomocą programu <i>kill</i>. Więcej informacji o programie kill można uzyskać pisząc:
<pre>
man kill
</pre>  
</p>
Jeśli proces został uruchomiony z konsoli to kombinacja klawiszy ctrl+c wysyła sygnał, po którym program zwykle kończy działanie. 
Programy w systemie Linux mogą mieć zdefiniowany sposób reakcji na odebrane sygnały, mogą je ignorować i kontunuować działanie. Sygnał nr 9 nie może być jednak zignorowany. Dlatego też często w celu zatrzymania (zakończenia) procesu wpisywane jest polecenie
<pre>
kill -9 NR_PROCESU
</pre>
</p>
<p>
Proces macierzysty może się dowiedzieć o sposobie zakończenia bezpośredniego potomka przez wywołanie funkcji systemowej <b>wait</b>. 
Jeśli wywołanie funkcji wait nastąpi przed zakończeniem potomka, przodek zostaje zawieszony w oczekiwaniu na to zakończenie.
Jeżeli proces macierzysty zakończy działanie przed procesem potomnym, to proces potomny nazywany jest sierotą (ang. orphan) i jest „adoptowany” przez proces systemowy init (wartość identyfikatora PID tego procesu jest równa 1), który staję się w ten sposób jego przodkiem. Jeżeli proces potomny zakończył działanie przed wywołaniem funkcji <b>wait</b> w procesie macierzystym, potomek pozostanie w stanie <i>zombi</i> (proces taki nazywany jest zombi, upiorem, duchem lub mumią). Zombi jest procesem, który zwalnia wszystkie zasoby (nie zajmuje pamięci, nie jest mu przydzielany procesor), zajmuje jedynie miejsce w tablicy procesów w jądrze systemu operacyjnego i zwalnia je dopiero w momencie wywołania funkcji wait przez proces macierzysty, lub w momencie zakończenia procesu macierzystego.
</p>
<p>
W ramach istniejącego procesu może nastąpić uruchomienie innego programu w wyniku wywołania jednej z funkcji systemowych <b>execl</b>, <b>execlp</b>, <b>execle</b>, <b>execv</b>, <b>execvp</b>, <b>execve</b>. Funkcje te będą określane ogólną nazwą <b>exec</b>. 
Za pomocą polecenia <i>man exec</i> można wyświetlić dokumentację tych funkcji.
Uruchomienie nowego programu oznacza w rzeczywistości zmianę programu wykonywanego dotychczas przez proces, czyli zastąpienie wykonywanego
programu innym programem, wskazanym odpowiednio w parametrach aktualnych funkcji exec.
Bezbłędne wykonanie funkcji exec oznacza zatem bezpowrotne zaprzestanie wykonywania bieżącego programu i rozpoczęcie wykonywania programu, którego nazwa jest przekazana jako argument. W konsekwencji, z funkcji systemowej <b>exec</b> nie ma powrotu do programu, gdzie nastąpiło jej wywołanie, o ile wykonanie tej funkcji nie zakończy się błędem.
Wyjście z funkcji exec można więc traktować jako jej błąd bez sprawdzania zwróconej wartości.
Funkcje służące do obsługi procesów zdefiniowane są w plikach sys/types.h oraz unistd.h.
</b>
<h2>Biblioteki w języku C do obsługi procesów</h2>
<h3>int fork(void);</h3>
<p>
Wartości zwracane:
<ul>
<li>poprawne wykonanie funkcji: utworzenie procesu potomnego; W procesie macierzystym funkcja
zwraca identyfikator (pid) procesu potomnego (wartość większą od 1), a w procesie potomnym
wartość 0.</li>
<li>zakończenie błędne: -1</li>
</ul>
Możliwe kody błędów zapisane są pod globalną zmienną <b>errno</b>.
</p>
<p>
W momencie wywołania funkcji (przez proces który właśnie staje się przodkiem) tworzony jest proces potomny, który wykonuje współbieżnie ze swoim przodkiem ten sam program. Potomek rozpoczyna wykonywanie programu od wyjścia z funkcji fork i kontynuuje wykonując kolejną instrukcję, znajdującą się w programie po wywołaniu funkcji fork. Do funkcji fork wchodzi zatem tylko proces macierzysty, a wychodzą z niej dwa procesy: macierzysty i potomny, przy czym każdy z nich otrzymuję inną wartość zwrotną funkcji fork. Wartością zwrotną funkcji fork w procesie macierzystym jest identyfikator (PID) potomka, a w procesie potomnym wartość 0. W przypadku
błędnego wykonania funkcji fork potomek nie zostanie utworzony, a proces wywołujący otrzyma wartość -1.
</p>
<h5>int getpid(void) oraz int getppid(void)</h5>
<p>
Wartości zwracane:
<ul>
<li>poprawne wykonanie funkcji: zwrócenie własnego identyfikatora (w przypadku funkcji getpid) lub
identyfikator procesu macierzystego (dla funkcji getppid).</li>
<li>zakończenie błędne: -1</li>
</ul>
</p>
<h3>void exit(int status)</h3>
<p>
Wartości zwracane:
<ul>
<li>poprawne wykonanie funkcji: przekazanie w odpowiednie miejsce tablicy procesów wartości status,
która może zostać odebrana i zinterpretowana przez proces macierzysty.</li>
<li>zakończenie błędne: -1</li>
</ul>
</p>
<p>
Funkcja kończy działanie procesu, który ją wykonał i powoduje przekazanie w odpowiednie miejsce tablicy procesów wartości status, która może zostać odebrana i zinterpretowana przez proces macierzysty. Jeśli proces macierzysty został zakończony, a istnieją procesy potomne – to wykonanie ich nie jest
zakłócone, ale ich identyfikator procesu macierzystego wszystkich procesów potomnych otrzyma wartość 1 będącą identyfikatorem procesu init (proces potomny staje się sierotą (ang. orphan) i jest „adoptowany” przez proces systemowy init). Funkcja exit zdefiniowana jest w pliku <i>stdlib.h</i>.
exit(0) – oznacza poprawne zakończenie wykonanie procesu
exit(dowolna wartość różna od 0 ) – oznacza wystąpienie błędu
</p>
<p>
Funkcja <b>exit</b> znajduje się w bibliotece <b>stdlib.h</b>. 
</p>
<h3>int wait(int *status) oraz int waitpid(int pid, int *status, int options)</h3>
<p>
Wartości zwracane:
<ul>
<li>poprawne wykonanie funkcji: identyfikator procesu potomnego, który się zakończył</li>
<li>zakończenie błędne: -1 lub 0 jeśli użyto opcji WNOHANG, a nie było dostępnego żadnego potomka</li>
</ul>
Argumenty funkcji:
<ul>
<li>status – status zakończenia procesu (w przypadku zakończenia w sposób normalny) lub numer sygnału w przypadku zabicia potomka lub wartość NULL, w przypadku gdy informacja o stanie zakończenia procesu nie jest istotna</li>
<li>pid – identyfikator potomka, na którego zakończenie czeka proces macierzysty 
 <ul>
  <li>pid &lt; -1 oznacza oczekiwanie na dowolny proces potomny, którego identyfikator grupy procesów jest równy modułowi wartości pid.</li>
  <li>pid = -1 oznacza oczekiwanie na dowolny proces potomny; jest to takie samo zachowanie, jakie stosuje funkcja wait.
  <li>pid = 0 oznacza oczekiwanie na każdy proces potomny, którego identyfikator grupy procesu jest równe identyfikatorowi wołającego procesu.
  <li>pid &gt; 0 oznacza oczekiwanie na potomka, którego identyfikator procesu jest równy wartości pid.
 </ul></li>
<li>options – jest sumą OR zera lub następujących stałych:
 <ul>
  <li>WNOHANG oznacza natychmiastowe zakończenie jeśli potomek się nie zakończył.</li>
  <li>WUNTRACED oznacza zakończenie także dla procesów potomnych, które się zatrzymały, a których status jeszcze nie został zgłoszony.</li>
 </ul>
</li>
</ul>
</p>
<p>
Oczekiwanie na zakończenie procesu potomnego. Funkcja zwraca identyfikator (pid) procesu, który się zakończył. Pod adresem wskazywanym przez zmienną status umieszczany jest status zakończenia, który zawiera albo numer sygnału (najmniej znaczące 7 bitów), albo właściwy status zakończenia (bardziej znaczący bajt).
Gdy działa parę procesów potomnych zakończenie jednego z nich powoduje powrót z funkcji wait.
Jeżeli funkcja wait zostanie wywołana w procesie macierzystym przed zakończeniem procesu potomnego, wykonywanie procesu macierzystego zostanie zawieszone do momentu zakończenia potomka. Jeżeli proces potomny zakończył działanie przed wywołaniem funkcji wait, powrót z funkcji wait nastąpi natychmiast, a w czasie pomiędzy zakończeniem potomka, a wywołaniem funkcji wait przez jego przodka potomek pozostanie w stanie zombi. Zombi nie jest tworzony, gdy proces macierzysty ignoruje sygnał SIGCLD.
</p>
<h3>rodzina funkcji exec</h3>
<p>
<ul>
<li>int execl ( char *path, char *arg0, ..., char *argn, char *null )</li>
<li>int execlp( char *file, char *arg0, ..., char *argn, char *null )</li>
<li>int execv ( char *path, char * argv[] )</li>
<li>int execvp( char *file, char * argv[] )</li>
<li>int execle( char *path, char *arg0, ..., char *argn, char *null, char *envp[])</li>
<li>int execve( char * path, char *argv[],char *envp[] )</li>
</ul>
Wartości zwracane:
<ul>
<li>poprawne wykonanie funkcji: wywołanie programu podanego jako parametr</li>
<li>zakończenie błędne: -1</li>
</ul>
Argumenty funkcji:
<ul>
 <li>path, file – pełna nazwa ścieżkowa lub nazwa pliku z programem</li>
 <li>arg0 ...argn – nazwa i argumenty programu który ma być wywołany</li>
</ul>
W ramach istniejącego procesu może nastąpić uruchomienie innego programu w wyniku wywołania jednej z funkcji systemowych execl, execlp, execle, execv, execvp, execve. Funkcje te określane są ogólną nazwą exec. Bezbłędne wykonanie funkcji exec oznacza bezpowrotne zaprzestanie wykonywania bieżącego programu i rozpoczęcie wykonywania programu, którego nazwa jest przekazana przez argument.
</p>
<p>
Różnice pomiędzy wywołaniami funkcji exec wynikają głównie z różnego sposobu budowy ich listy argumentów: w przypadku funkcji execl i execlp są one podane w postaci listy, a w przypadku funkcji execv i execvp jako tablica. Zarówno lista argumentów, jak i tablica wskaźników musi być zakończona wartością NULL. Funkcja execle dodatkowo ustala środowisko wykonywanego procesu. 
<br>
Funkcje execlp oraz execvp szukają pliku wykonywalnego na podstawie ścieżki przeszukiwania podanej w zmiennej środowiskowej PATH. Jeśli zmienna ta nie istnieje, przyjmowana jest domyślna ścieżka :/bin:/usr/bin.
<br>
Wartością zwrotną funkcji typu exec jest status, przy czym jest ona zwracana tylko wtedy, gdy funkcja zakończy się niepoprawnie, będzie to zatem wartość –1.
Funkcje exec nie tworzą nowego procesu, tak jak w przypadku funkcji fork!!!
</p>
<p>
Przykłady:
<?php
$source = '
execl(„/bin/ls”, „ls”, „-l”,null);
execlp(„ls”, „ls”, „-l”,null);

char* const av[]={„ls”, „-l”, null};
execv(„/bin/ls”, av);

char* const av[]={„ls”, „-l”, null};
execvp(„ls”, av);
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<h1>Przykłady</h1>
<h2>Tworzenie procesu potomnego</h2>
<p>
Program jest początkowo wykonywany przez jeden proces. W wyniku wywołania funkcji systemowej fork następuje rozwidlenie i tworzony jest proces potomny, który
kontynuuje wykonywanie programu swojego przodka od miejsca utworzenia.
</p>
<?php
$source = '
#include <stdio.h>
#include <unistd.h>

void main()
{
  printf("Początek\n");
  fork();
  printf("Koniec\n");
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
Wynik działania programu jest następujący:
<pre>
Początek
Koniec
Koniec
</pre>
<h2>Wykonanie innego programu</h2>
<p>
W wyniku wywołania funkcji systemowej execlp następuje zmiana wykonywanego programu, zanim sterowanie dojdzie do instrukcji wyprowadzenia napisu Koniec. Zmiana wykonywanego programu powoduje, że sterowanie nie wraca już do poprzedniego programu i napis "Koniec" nie zostanie wyświetlony.
</p>
<?php
$source = '
#include <stdio.h>
#include <unistd.h>

void main()
{
  printf("Poczatek\n");
  execlp("ls", "ls", "-a", NULL);
  printf("Koniec\n");
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<p>Program można rozbudować tworząc proces potomny, który wykona zewnętrzny program. Proces potomny może za pomocą potoku przekazać rezultat swojego działania do programu macierzystego. Zagadnienia związane z potokami nie będą poruszane na dzisiejszych zajęciach.</p>
<h2>Utworzenie procesu potomnego, który wykona zewnętrzny program</h2>
<p>
W programie zostaje utworzony proces potomny. Na podstawie wartości zwróconej przez funkcję fork określone są czynności jakie wykona proces potomny, a jakie proces macierzysty. Proces potomny wykona zewnętrzny program, a program macierzysty poczeka na zakończenie procesu potomnego i wyświetli napis "Koniec".
<br>
W programie została użyta funkcja exit, zawarta w bibliotece stdlibc.h.
</p>

<?php
$source = '
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/wait.h>

void main()
{
  printf("Poczatek\n");
  if (fork() == 0)
  {
    execlp("ls", "ls", "-a", NULL);
    perror("Blad uruchmienia programu");
    exit(1);
  }
  wait(NULL);
  printf("Koniec\n");
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h2>Pobieranie informacji o procesie potomnym i macierzystym</h2>
<p>
W programie zostaje utworzony proces potomny. Na podstawie wartości zwróconej przez funkcję fork zostaje zdefiniowany kod, który wykona proces potomny. Proces potomny wypisze odpowiednie powitanie, a następnie będzie dzekał 9 sekund. Po 9 sekundach proces potomny zakończy swoje działanie zwracając wartość 7, która oznacza niepoprawne zakończenie procesu.
<br>
Proces macierzysty odczeka 1 sekundę po czym wyśle sygnał nr 9 do procesu potomnego. Po otrzymaniu tego sygnału proces potomny natychmiast zakończy swoje działanie. Proces macierzysty czeka na zakończenie procesu potomnego i zwraca wartość, jaką zwrócił proces potomny. 
</p>
<?php
$source = '
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/wait.h>

void main()
{
  printf("Uruchomienie procesu o identyfikatorze %d\n", getpid()); 
  int pid1, pid2, status;
  pid1 = fork();
  if (pid1 == 0) 
  { /* proces potomny */
    printf("Jestem procesem potomnym o identyfikatorze %d, mam przodka o identyfikatorze %d\n", 
      getpid(), getppid());
    sleep(10);
    printf("Proces potomny wybudza się");
    exit(7);
  }
  /* proces macierzysty 
     Ta część kodu jest nieosiągalna dla procesu potomnego*/
  
  //Usuwanie procesu potomnego
  sleep(1);
  kill(pid1, 9);
  pid2 = wait(&status);
  printf("Status zakonczenia procesu %d: %x\n", pid2, status);
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h2>Tworzenie procesu sieroty</h2>
<?php
$source = '
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
void main()
{
  if (fork() == 0)
  {
    sleep(30);
    exit(0);
  }
  exit(0);
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h2>Tworzenie procesu zombie</h2>
<?php
$source = '
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

void main()
{
  if (fork() == 0)
  {
    exit(0);
  }
  sleep(30);
  wait(NULL);
  //Proces Zombie przestaje istnieć
  exit(0);
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h2>Zadania do samodzielnego wykonania</h2>
<h2>Zadanie 1</h2>
Napisz program, który uruchomi przeglądarkę z twoją ulubioną strona WWW. 
Przeglądarkę wraz z podaniem strony jaką ma otworzyć można uruchomnić za pomocą polecenia:
<pre>
firefox adam.kaliszan.yum.pl
</pre>
<h2>Zadanie 2</h2>
Napisz program, który tworzy proces potomny. Następnie tylko proces potomny tworzy kolejny proces potomny. Za pomocą funkcji sleep wymuś, by procesy działały 30 sek. Za pomocą polecenia pstree sprawdź jak wyglądają relacje pomiędzy procesami. W tym celu uruchom program w następujący sposób:
<pre>
./zadanie2 &
</pre> 
<h2>Zadanie 3</h2>
Napisz program którego rezultatem będzie wydruk zawartości bieżącego katalogu poprzedzony napisem „Początek” a zakończony napisem „Koniec”. Wypisanie napisu koniec przez listą plików jest niedopuszczalne.
</body>

<!--
<h1>Rozwiązania dla zadań z pliku pdf</h1>
Zadanie 2
<?php
$source = '
#include<stdio.h>
#include<unistd.h>
#include<sys/wait.h>

int main(int argc, char **argv)
{
  int idTab[4];
  for (int i=0; i<4; i++)
  {
    idTab[i] = fork();
    if (idTab[i] == 0)
    {
      printf("Jestem procesem potomnym nr %d, mój przodek to %d\n", getpid(), getppid());
      sleep(10);
      return i;
    }
  }

  for (int i=0; i<4; i++)
  {
    int rezultat;
    waitpid(idTab[i], &rezultat, 0);
    if (WIFEXITED(rezultat))
      printf("Proces potomny %d zakończył się %d\n", idTab[i], WEXITSTATUS(rezultat));
    else
      printf("Proces potomny %d został przerwany\n", idTab[i]);
  }  

  printf("Jestem procesem nr %d, mój przodek to %d\n", getpid(), getppid());

  return 1;
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
Zadanie 3
<?php
$source = '
#include<stdio.h>
#include<unistd.h>
#include<sys/wait.h>

int main(int argc, char **argv)
{
  int idTab[4];
  for (int i=0; i<4; i++)
  {
    idTab[i] = fork();
    if (idTab[i] == 0)
    {
      printf("Jestem procesem potomnym nr %d, mój przodek to %d\n", getpid(), getppid());
      //sleep(10);
    }
    else
    {
      int rezultat;
      waitpid(idTab[i], &rezultat, 0);
      if (WIFEXITED(rezultat))
        printf("Proces potomny %d zakończył się %d\n", idTab[i], WEXITSTATUS(rezultat));
      else
        printf("Proces potomny %d został przerwany\n", idTab[i]);
      sleep(2);
      return i;
    }
  }

  return 4;
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>



-->


