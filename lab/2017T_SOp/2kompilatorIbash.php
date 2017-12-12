<?php
include_once '../../class.geshi.php';
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<h3>Wstęp oraz logowanie do systemu</h3>
<p>Systemy operacyjne Unix są systemami wielodostępnymi i wielozadaniowymi - wielodostępność pozwala na
jednoczesną pracę wielu użytkowników, a wielozadaniowość umożliwia uruchamianie w systemie wielu zadań
jednocześnie. Wielodostępność wymaga stosowania mechanizmów, które pozwalają na bezpieczną i jednoczesną 
pracę różnym użytkownikom. Podstawowym rozwiązaniem w tym zakresie jest autoryzacja, którą zrealizowano
w oparciu o nazwy użytkowników i ich hasła. 
Wiele rozwiązań z systemu Unix zostało wykorzystane w systemie Linux, dlatego też systemy te potocznie zwane są systemami <a href="http://www.unix.org/what_is_unix/history_timeline.html">Unix</a> czy Nix, pomimo iż jest to zupełnie na nowo napisany kod, w którym wzorowano się również na systemie Minix oraz BSD</a>. 

Rozpoczęcie pracy z systemem operacyjnym wymaga zatem podania tych 
dwóch informacji: loginu i hasła (<b>student</b>, <b>student</b>).
</p>
<p>

<h3>Sprawdzenie działania kompilatora</h3>
W systemie Linux za pomocą konsoli można: utworzyć plik, edytując plik napisać program w języku C, skompilować napisany program uruchomić go.
<h4>Tworzenie pliku</h4>
<p>
Programy w języku C zapisane są w plikach z rozszerzeniem <b>.c</b>. Pliki nagłówkowe mają natomiast rozszerzenie <b>.h</b>. 
Plik można utworzyć za pomocą polecenia <b>touch</b>. Istnieje wiele innych sposobów utworzenia pliku.
<pre>
touch helloWorld.c
</pre>
</p>
<h4>Edycja pliku</h4>
<p>
W systemie Linux dostępnych jest wiele edytorów: vi, mcedit, pico. Dla laika korzystającego z konsoli najłatwiej jest edytować plik w programie <b>mcedit</b> lub <b>pico</b>. Program pico i vi jest zwykle zainstalowany w najpopularniejszych dystrybucjach systemu Linux. W przypadku wersji minimalistycznej dostępny jest zwykle program vi. Za pomocą konsoli można otworzyć również plik w programie graficznym. Najlepiej jest to zrobić w tle tak, by nie blokować dostępu do komsoli.
</p>
Otwarcie pliku za pomocą edytora działającego w trybie tekstowym:
<pre>
pico helloWorld.c
</pre>
Otwarcie pliku za pomocą edytora działającego w trybie graficznym:
<pre>
xed helloWorld.c &
</pre>
lub
<pre>
gedit helloWorld.c &
</pre>

Po otwarciu pliku wpisz do niego następujący kod:
<?php
$source = '
#include <stdio.h>
int main(int argc, char **argv)
{
  printf("To jest test\n");

  return 0;
}
'; 
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
<h2>Kompilacja pliku</h2>
<p>
Do kompilacji kodu napisanego w języku C służy program <b>gcc</b>.
</p>
Program najprościej skompilować pisząc:
<pre>
gcc helloWorld.c
</pre>
W wyniku kompilacji zostanie utworzony plik <b>a.out</b>, który można uruchomić. W celu nadania odpowiedniej nazwy w czasie kompilacji, należy zastosować opcję -o:
<pre>
gcc helloWorld.c -o helloWorld
</pre>
<h2>Uruchomienie programu</h2>
W celu uruchomienia programu, który zapisany jest w pliku znajdującym się w bieżącym katalogu należy wpisać następujące polecenie:
<pre>
./NAZWA_PLIKU_Z_PROGRAMEM
</pre>
np.
<pre>
./a.out
</pre>
lub
<pre>
./helloWorld
</pre>

<p>W celu sprawdzenia działania kompilatora przetłumacz poprzedni program na język procesora
<br><i>gcc helloWorld.c –o hello</i>.
</p>
<p>
Polecenia należy wydawać z wiersza poleceń. W tym celu otwórz okno konsoli: Programy->Akcesoria->Terminal (lewy ctrl + lewy alt + t).
By sprawdzić czy program działa, uruchom go (polecenie ./hello)
</p>
<h3>Logowanie i wielodostępność</h3>
<p>
Wielodostępność oznacza także, że każdy użytkownik może zalogować się do systemu wielokrotnie, 
korzystając np. z terminali wirtualnych dostępnych za pomocą klawiszy Alt-F1, Alt-F2 itd. lub Ctrl-Alt-F1, Ctrl-Alt-F2 itd. 
Możliwe jest także pozyskanie informacji o wszystkich zalogowanych aktualnie w systemie użytkownikach - aby tego 
dokonać należy wydać jedno z poleceń:
<br><b>who</b> lub <b>finger</b>.
</p>
<p>
Wylogowanie się z systemu jest możliwe po wydaniu polecenia:
<br><b>exit</b>
</p>
<p>
W systemach UNIX rozróżniane są wielkie i małe litery: dotyczy to zarówno logowania do systemu jaki i wydawania 
poleceń w systemie oraz nazw plików i katalogów. 
</p>
<h3>Pomoc systemowa</h3>
<p>
W systemach Unix dostępna jest pomoc systemowa w postaci dokumentów tekstowych opisujących różne aspekty systemu i 
jego narzędzia. Dostęp do dokumentacji możliwy jest dzięki przeglądarce interaktywnej, którą uruchamia się poleceniem:
<br><b>man nazwa_polecenia</b>
<br>Wyszukiwanie stron pomocy systemowej jest możliwe dzięki programom **apropos** oraz **whatis**, które wyszukują
podanych słów w pomocy systemowej, np.: 
<br><b>apropos passwd</b>
<br><b>whatis passwd</b>
</p>

<h3>Wydawanie poleceń</h3>
<p>
Polecenia w systemach Unix wydaje się wpisując nazwę polecenia oraz wybierając klawisz Enter. 
Polecenia mogą przyjmować argumenty oraz przełączniki, zgodnie ze wzorcem: 
<br>polecenie [przełączniki] [argumenty]
</p>
<p>
Oto przykład: 
<br><b>ls -al /etc</b>
<br>W tym przypadku polecenie <b>ls</b> zostało uruchomione z argumentem <b>/etc</b> oraz z przełącznikami <b>al</b>.
Wydanie polecenia ls bez argumentu spowoduje wyświetlenie zawartości aktualnego katalogu;
podanie natomiast jako argumentu nazwy katalogu, 
spowoduje wyświetlenie zawartości tego, wskazanego katalogu - w przykładzie jest to katalog /etc. 
</p>
<p>
Dalej, domyślne działanie polecenia można modyfikować stosując przełączniki, które są przeważnie jednoznakowymi skrótami,
zawsze występującymi po znaku "-". W przykładzie zastosowano dwa przełączniki a i .
Opis możliwych do zastosowania argumentów i przełączników można odnaleźć w pomocy systemowej dla właściwego polecenia.
Przełączniki, jak widać, można łączyć - kolejny przykład przedstawia jak można wydawać polecenie z przełącznikami, np:
<ul>
 <li>ls -a -l /etc</li>
 <li>ls -l -a /etc</li>
 <li>ls -al /etc</li>
 <li>ls -la /etc</li>
</ul>
</p>

<h3>Struktura katalogów</h3>
<p>Najistotniejsze podkatalogi katalogu głównego: 
<ul>
 <li>/bin - katalog zawierający programy niezbędne do uruchomienia systemu,</li> 
 <li>/dev - katalog zawierający pliki specjalne, które reprezentują dostępne urządzenia,</li> 
 <li>/etc - katalog z lokalnymi plikami konfiguracyjnymi systemu,</li> 
 <li>/home - w tym katalog znajdują się podkatalogi domowe użytkowników systemu,</li> 
 <li>/proc - wirtualny system plików, który dostarcza informacji o bieżących procesach w systemie i jego jądrze,</li> 
 <li>/root - zwyczajowo katalog domowy użytkownika //root//, czyli administratora systemu</li> 
 <li>/usr - katalog zawierający zestaw oprogramowania użytkowego dostępnego dla użytkowników</li> 
 <li>/var - katalog ten zawiera pliki, które często zmieniają swoją zawartość</li>
</ul>
</p>

<h3>Obsługa katalogów</h3>
<p>
Podstawowe operacje obsługi katalogów można realizować z wykorzystaniem następujących poleceń: 
<ul>
<li> cd [przełączniki] nazwa_katalogu - zmiana katalogu, np.:
 <ul>
  <li>cd /etc - zmienia katalog bieżący na katalog /etc;</li>
  <li>cd ~ - zmienia katalog bieżący na katalog domowy użytkownika;</li> 
  <li>cd .. - zmienia katalog bieżący na katalog bezpośrednio nadrzędny (przejście o 1 katalog w góre).</li> 
 </ul></li>
<li><b>ls</b> [przełączniki] [nazwa_katalogu] - wyświetlenie zawartości katalogu, np.:
 <ul> 
  <li>ls - wyświetla zawartość katalogu bieżącego;</li> 
  <li>ls -a - wyświetla zawartość katalogu bieżącego uwzględniając wszystkie pliki - tzn. także te, których nazwa zaczyna się od znaku "." (umownie są to pliki ukryte, tzw. dotted files);</li> 
  <li>ls -al - wyświetla wszystkie pliki z katalogu bieżącego z uwzględnieniem tzw. "długiego formatu", czyli podając typ każdego obiektu w katalogu:
   <ul>
    <li>pierwszy znak:
     <ul>
      <li><b>d</b> - katalog,</li>
      <li><b>-</b> - plik zwykły,</li>
      <li><b>l</b> - dowiązanie), prawa dostępu, liczbę dowiązań,
     </ul>
    </li>
    <li>właściciela,</li>
    <li>nazwę grupy,</li>
    <li>rozmiar (w bajtach),</li>
    <li>data ostatniej modyfikacji,</li>
    <li>nazwa.</li>
   </ul>
  </li> 
 </ul></li>
<li><b>mkdir</b> [przełączniki] nazwa_katalogu - tworzenie katalogów</li> 
<li><b>rmdir</b> [przełączniki] nazwa_katalogu- usuwanie katalogów, np.:
 <ul> 
  <li>rmdir ~/xyz - usunięcie katalogu xyz z katalogu domowego;</li> 
  <li>rmdir ./xyz - usunięcie katalogu xyz z katalogu bieżącego.</li>
 </ul></li>
</ul>
Dla poleceń rmdir i mkdir dostępny jest m.in. przełącznik -p, który pozwala odpowiednio, usuwać i tworzyć struktury katalogów, np.:
<br><b>rmdir -p abc/def/ghi</b> - usunie katalogi ghi, def oraz abc, które tworzyły hierarchię. 
</p>

<h3>Obsługa plików</h3>

Plik to zdefiniowana (przeważnie przez użytkownika) porcja danych, która jest przechowywana w systemie w pamięci masowej. W systemach UNIX niemal wszystko jest plikiem, także urządzenia są reprezentowane przez specjalne pliki. Pozwala to, na zachowanie spójnego sposobu dostępu i obsługi do wielu heterogenicznych zasobów, w jeden transparentny sposób. Nazwy plików nie mają podziału na nazwę i rozszerzenie, jednakże można takie podejście stosować; możliwe jest stosowanie w nazwach plików znaków specjalnych (np.: $, % lub #), ale nie jest to zalecane. 
Podstawowe operacje obsługi plików można realizować z wykorzystaniem następujących poleceń:
<ul>
 <li><b>cp</b> [przełączniki] nazwa_pliku nowa_nazwa_lub_katalog - kopiowanie pliku określonego przez pierwszy argument pod nazwę lub do katalogu określonego drugim argumentem, np.:
  <ul>
   <li>cp plik1.txt plik2.txt - kopiuje plik plik1.txt pod nową nazwę plik2.txt w katalogu bieżącym;</li> 
   <li>cp /tmp/plik1.txt ~ - kopiuje plik plik1.txt z katalogu /tmp do katalogu domowego użytkownika; ~ oznacza katalog domowy;</li> 
   <li>cp plik1.txt ~/plik2.txt - kopiuje plik plik1.txt z katalogu bieżącego pod nową nazwę plik2.txt w katalogu domowym użytkownika.</li>
  </ul>
Przydatnym przełącznikiem polecenia cp jest przełącznik -r, który służy do kopiowania całych struktur katalogów. 
 </li>
 <li><b>rm</b> [przełączniki] lista_plików -usuwanie plików podanych jako argumenty wywołania, np.:
  <ul>
   <li>rm abc.txt xyz.txt - usuwa pliki abc.txt i xyz.txt w katalogu bieżącym;</li>
   <li>rm /tmp/abc.txt - usuwa plik abc.txt z katalogu /tmp;</li>
  </ul>
Przydatnym przełącznikiem polecenia rm jest przełącznik -r, który służy do usuwania całych struktur katalogów.
 </li>
 <li><b>mv</b> [przełączniki] nazwa_pliku nowa_nazwa - zmiana nazwy pliku określonego pierwszym argumentem wywołania na nazwę określoną drugim argumentem wywołania. Jeśli drugi argument wywołania jest katalogiem, to wówczas plik zostanie przeniesiony do tego katalogu, np.:
  <ul>
   <li>mv abc.txt xyz.txt - zmiana nazwy pliku abc.txt na nazwę xyz.txt w katalogu bieżącym;</li> 
   <li>mv /tmp/abc.txt ~ - przeniesienie pliku abc.txt z katalogu /tmp do katalogu domowego użytkownika.</li>
  </ul>
 </li> 
 <li><b>touch</b> [przełączniki] nazwa_pliku - modyfikuje informacje na temat czasów modyfikacji i odczytu pliku, ale pozwala także na utworzenie pliku, np.: 
  <ul>
   <li>touch abc.txt - utworzenie (pustego) pliku abc.txt w katalogu bieżącym.</li>
  </ul>
 </li>
</ul> 
Polecenia dotyczące plików (i katalogów) można także wydawać z wykorzystaniem tzw. wzorców uogólniających, które tworzy się z zastosowaniem następujących operatorów:
<ul>
 <li>* - zastępuje dowolny ciąg znaków (także pusty);</li>
 <li>? - zastępuje dokładnie jeden dowolny znak;</li>
 <li>[<znaki>] - zastępuje dokładnie jeden znak z podanego zakresu, np.: [abc];</li>
 <li>[^<znaki>] - znak ^ na początku oznacza dopełnienie zbioru, czyli dla przykładu [^xyz], oznacza dowolny znak nie będący literą x, y i z.
</ul>
Przykłady poleceń z wykorzystaniem wzorców uogólniających: 
<ul>
 <li>cp ./*.txt ~ - kopiowanie wszystkich plików z rozszerzeniem .txt z katalogu bieżącego do katalogu domowego użytkownika;</li>
 <li>rm ./[0-9]* - usunięcie wszystkich plików z katalogu domowego, których nazwa rozpoczyna się od cyfry.</li>
</ul>
<h3>Wyszukiwanie plików</h3>
<p>Jak już wspomniano pliki w systemach UNIX są używane do przechowywania danych użytkowników oraz reprezentują m.in. niektóre urządzenia systemowe, istotne jest zatem sprawne wyszukiwanie i lokalizowanie plików w strukturze katalogów. Zadanie to można zrealizować na kilka sposobów, w zależności od charakteru poszukiwanego pliku i kryteriów wyszukiwania. Lokalizacji plików wykonywalnych - programów - można dokonać z wykorzystaniem polecenia: <b>whereis</b> [przełączniki] lista_programów
<br>Np.: whereis ls
</p>
<p>Do wyszukiwania plików można także zastosować polecenie: 
<br><b>locate</b> [przełączniki] wzorzec
<br>Program ten wyszukuje pliki, podając listę plików ze ścieżkami, których nazwa zostanie dopasowana do podanego jako argument wywołania wzorca. Program locate zwraca wyniki niemal natychmiast, ponieważ wyszukiwanie faktycznie odbywa się na przygotowanej wcześniej bazie plików (nie jest przeszukiwana cała struktura katalogów). Baza ta (indeks nazw plików), przeważnie jest aktualizowana raz na dobę - oznacza to, że wynik może nie uwzględniać zmian w systemie plików i katalogów, jakie zostały wykonane po ostatniej aktualizacji spisu (aktualizacji może zawsze dokonać administrator systemu wykorzystując polecenie updatedb). Wzorzec zapytania można budować z wykorzystaniem operatorów uogólniających - wówczas należy umieścić wzorzec w cudzysłowie, aby nie został on rozwinięty przez interpreter poleceń.
</p>
<h3>Prawa dostępu</h3>
<p>
W systemach UNIX dostęp do plików i katalogów zabezpieczony jest tzw. prawami dostępu, które regulują zasady na jakich użytkownicy mogą korzystać z tych zasobów. Wyróżnia się trzy rodzaje praw: prawo odczytu - oznaczane r (ang. read), prawo zapisu - oznaczane w (ang. write) oraz prawo wykonania - oznaczane x (ang. execute). Takie prawa są określane niezależnie dla: użytkownika, który jest właścicielem pliku lub katalogu (domyślnie właścicielem jest użytkownik, który utworzył dany plik lub katalog); użytkowników, którzy należą do tej samej grupy, do której należy plik lub katalog oraz dla pozostałych użytkowników. Interpretacja praw dostępu jest następująca: 
<pre>
Czynność do wykonania                        Prawa do pliku   Prawa do katalogu 	   
Przeglądanie zawartości katalogu             ---              r-- 	   
Utworzenie pliku w katalogu                  ---              -wx
Zmiana nazwy pliku w katalogu                ---              -wx
Usunięcie pliku z katalogu                   ---              -wx
Odczytanie zawartości pliku                  r--              --x
Zapis do pliku                               -w-              --x  
Wykonanie pliku (np. programu lub skryptu)   --x              --x 	 
</pre>
Jak już wspomniano informacje o prawach dostępu można uzyskać dzięki poleceniu ls z przełącznikiem -l - oto przykład oraz jego interpretacja: 
ls -l
<pre>
sdrwx------ 15 student students 4096 lip  2 11:27 ./
drwxr-xr-x  54 student students 4096 lip  2 19:20 ../
-rwxr--r-x  2  student students 4096 cze 21 23:32 plik1.txt
</pre>
Informacja o prawach wyświetlana jest według następującego schematu
<pre> 
Użytkownik (user)       Grupa (group) 	        Inni użytkownicy (others) 	   
r 	w 	x 	r 	w 	x 	r 	w 	x 	 
</pre>
Zatem dla pliku plik1.txt dostępne są następujące prawa (znak "-" oznacza brak danego prawa): dla właściciela dostępne są wszystkie prawa, dla członków grupy students dostępne jest tylko prawo do odczytu, a dla pozostałych użytkowników prawa odczytu i wykonywania.
</p>
<p>
Prawami dostępu można także operować stosując notację numeryczną, w której każde prawo ma przypisaną pewną wartość liczbową, i tak:
<ul>
 <li>prawo odczytu - 4,</li>
 <li>prawo zapisu - 2,</li>
 <li>prawo wykonywania - 1.</li>
</ul>
Tak więc, prawa zapisane numerycznie dla pliku pik1.txt z powyższego przykładu miałyby następującą postać: 745
<ul>
 <li>7 oznacza wszystkie prawa dla użytkownika (4 + 2 + 1),</li>
 <li>4 oznacza prawo odczytu dla grupy,</li>
 <li>5 oznacza praw odczytu i wykonywania (4 + 1) dla pozostałych użytkowników.
</ul> 
Operowanie prawami dostępu i określaniem prawa własności jest możliwe dzięki następującym poleceniom systemowym:
<ul>
 <li><b>chmod</b> [przełączniki] uprawnienia nazwa_pliku_lub_katalogu - zmiana praw dostępu wskazanych pierwszym argumentem wywołania dla pliku lub katalogu wskazanym drugim argumentem wywołania; w specyfikacji należy, zatem wskazać (i) dla kogo mają być zmienione prawe (u - właściciel, g - użytkownicy z tej samej grupy, o - inni użytkownicy, a - wszyscy), (ii) rodzaj zmiany (+ - dodanie praw, - - odjęcie praw, = - ustalenie praw) oraz (iii) prawa. Oto przykładowe zlecenia z wykorzystaniem polecenia chmod:
  <ul>
   <li>chmod u+w plik.txt - dodaje prawo odczytu dla właściciela do pliku plik.txt;</li>
   <li>chmod go-x plik.txt - usuwa prawo wykonywania dla użytkowników z tej samej grupy i innych do pliku plik.txt;</li> 
   <li>chmod a=r plik.txt - ustawia prawa dostępu na tylko do odczytu dla wszystkich użytkowników do pliku plik.txt;</li>
  </ul>
Polecenie chmod umożliwia także określanie praw dostępu w postaci numerycznej, np.: 
  <ul>
   <li>chmod 777 plik.txt - ustawia wszystkie prawa, wszystkim użytkownikom do pliku plik.txt;</li>
   <li>chmod 742 - ustawia prawa odczytu, zapisu i wykonywania właścicielowi, prawo odczytu użytkownikom z tej samej grupy oraz prawo zapisu innym użytkownikom do pliku plik.txt;</li>
  </ul>
 </li>
 <li><b>chown</b> [przełączniki] nazwa_nowego_właściciela nazwa_pliku_lub_katalogu - zmiana właściciela pliku lub katalogu. Ze względu na nieodwracalność·	 ewentualnych zmian, polecenie to jest często zarezerwowane dla administratora systemu.</li>
 <li><b>chgrp</b> [przełączniki] nazwa_nowej_grupy nazwa_pliku_lub_katalogu - zmienia grupę, do której należy wskazany plik lub katalog. Podobnie jak polecenie chown, i to polecenie najczęściej jest zarezerwowane dla administratora.</li>
</ul>
</p>
<h3>Dowiązania</h3>
<p>
W systemach UNIX informacje o plikach na dysku przechowywane są w strukturach, które nazywa się i-węzłami (ang. i-node) - każdy taki i-węzeł przechowuje m.in. następujące informacje: prawa dostępu, daty ostatnich modyfikacji, licznik dowiązań oraz dodatkowe atrybuty. Licznik dowiązań określa ile razy dany plik dostępny jest w systemie plików - być może w różnych katalog pod różnymi nazwami. Licznik ten umożliwia realizację dowiązań do plików, które z kolei są dodatkowymi nazwami dla pliku, umożliwiającymi dostęp do oryginału (np. z poziomu różnych katalogów). 
Istnieją dwa rodzaje dowiązań: tzw. dowiązania twarde (ang. hard links) oraz tzw. dowiązania miękkie lub symboliczne (ang. soft or symbolic links). Dowiązania symboliczne mogą także dotyczyć katalogów, oraz plików w innych systemach plików - informacja o nich dostępna jest dzięki omówionemu już poleceniu ls -l. Wszystkie dowiązania można przetwarzać dokładnie tak samo jak pliki zwykłe, w szczególności mogą także być usunięte poleceniem rm. 
Tworzenie dowiązań jest możliwe dzięki poleceniu: 
<br>
<b>ln</b> [przełączniki] źródło nazwa_dowiązania
<br>
pierwszy argument musi wskazywać na istniejący plik (lub katalog w przypadku dowiązań symbolicznych), do którego tworzone jest dowiązanie, a drugim argumentem jest nowa nazwa dla tego pliku. Utworzenie dowiązania symbolicznego wymaga zastosowania przełącznika -s. Przykładowe wywołania zlecenia utworzenia dowiązań: 
<ul>
 <li>ln ./abc/plik.txt plik1.txt - tworzy dowiązanie (twarde) do pliku plik.txt w katalogu ./abc pod nazwą plik1.txt w katalogu bieżącym;</li> 
 <li>ln -s ./abc/plik.txt ~/plik1.txt - tworzy dowiązanie symboliczne do pliku plik.txt w katalogu ./abc pod nazwą plik1.txt w katalogu domowym użytkownika.</li>
</ul>
</p>
</body>

