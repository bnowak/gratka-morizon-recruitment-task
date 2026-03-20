## Założenia

- Skoro samo zadanie oraz README.md jest napisane w języku polskim, to ten dokument też napiszę w tym języku.

## Architektura

Jest to dosyć mały prosty/projekt z użyciem architektury MVC.  
Mając na uwadze ograniczony czas oraz ilość poprawek/zadań do wykonania pozostanę przy tej architekturze.
Natomiast miejscami jest ona niespójna, co postaram się usprawnić oraz zwiększyć powtarzalność rozwiązań.
Mając więcej czasu pokusiłbym się o skorzystanie z architektury hexagonalnej (oraz być może warstwy z domeną), gdyż
zauważyłem pewne elementy biznesowe jak:

- dane i zależności: zdjęcia, polubienia, użytkownik
- logikę/akcje: polub/"odlub" :P zdjęcie, import zdjęć

Natomiast zdaję sobię sprawę że dla projektu tej skali jest to lekki "overengineering" :P.

- [ ] niespójne podejście z Like component (wydzielony osobno) a reszta encji/repo wydzielona bardziej technicznie
- [ ] nie powinniśmy przekazywać do widoków bezpośrednio modeli z bazy. Albo przekazujemy wartości skalarne, albo
  tworzymy obiekty DTO jako warstwa pośrednia

## Testy

- [x] funkcjonalne
- [x] integracyjne
- [ ] jednostkowe

## Zadania

- [ ] Zadanie 1 - zadbaj o jakość kodu oraz rozwiązań w projekcie SymfonyApp
    - [x] poprawki w composer.json
    - [x] poprawki w Dockerfiles + entrypoint (zmergowanie części wspólnych, uprawnienia usera)
    - [ ] aktualizacja PHP do 8.5
        - [ ] wszystkie możliwe klasy `final readonly`
    - [ ] sprawdzić walidacje schematu bazy
    - [ ] aktualizacja Symfony do 8.0
    - [ ] aktualizacja zależności do nowszych wersji
    - [ ] dodanie php code sniffer
    - [ ] dodanie phpstan
    - [ ] dodanie deptrac dla trackowania zależności pomiędzy komponentami
    - [ ] lepsza obsługa wyjątków w \App\Likes\LikeService
    - [x] pozbycie się \App\Likes\LikeRepository::setUser oraz stanowego property $user, jawne użycie user w zależnych
      metodach jako argument
    - [ ] użycie timestampable w \App\Likes\Like::$createdAt
    - [x] brak transakcyjności (w kontekści countera like'ów) w akcji polubiania/odlubiania zdjęcia przez usera.  
      Tutaj dodam że do samego countera można by podejść inaczej:
      - usunąć go całkowicie, i opierać się na ilość relacji pomiędzy encjami Like <-> Photo.
        Mamy wtedy spójność danych zapewnioną przez bazę danych (zawsze prawidłowy stan).
        Dla poprawy wydajności, aby doctrine nie czytał każdorazowo kolekcji like'ów z poziomu encji Photo możemy użyć https://www.doctrine-project.org/projects/doctrine-orm/en/3.6/tutorials/extra-lazy-associations.html
        Dzięki temu wołając `count` na kolekcji like'ów (obiektowo poprzez endję), doctrine wywoła pod spodem jednorazowo `SELECT COUNT(*)` bez wczytywania całej kolekcji danych. 
        Jest to podejście z znormalizowaną bazą danych (brak dublowania informacji w róznych tabelkach).
      - innym podejściem mogłoby być pozostawienie countera w bazie, natomiast użycia Doctrine events.
        W reakcji na operację dodania/usunięcia Like doctrine samoczynnie by aktualizował counter.
        Zabezpieczyłoby nas to prze ew. wywołaniem metod `removeLike`/`createLike` z LikeRepository (z pominięciem domenowego LikeService).
        Na minus jest fakt, że jest to bardziej techniczne rozwiązanie i "ukryta" logika biznesowa bardziej na warstwie infrastruktury.
        Jest to podejście ze denormalizowaną bazą danych (duble w bazie z przeznaczeniem pod performance odczytu, brak join'ów).
    - [x] ustawić firewall na akcje/endpointy publiczne i dostępne po zalogowaniu
    - [x] użyć #[Template] atrybutów pod widoki
    - [x] w kontrolerach wstrzykiwać bezpośrednio konkretne repozytoria zamiast poprzez EntityManager
    - [x] \App\Controller\ProfileController::profile pobrać id usera z sesji bezpośrednio a nie poprzez Request
    - [x] stworzyć helper metodę? do pobrania zalogowanego usera w kontrolerach => użyłem wbudowanej w symfony
    - [x] stworzyć AuthTokenRepository oraz UserRepository i wynieść zapytania SQL z \App\Controller\AuthController::
      login do nich, zaadresować SQJ injection
    - [x] użyć mechanizmów Symfony do pokrycia endpointów autoryzacji z \App\Controller\AuthController
    - [ ] obsłużyć porzucone biblioteki raportowane przez composera
    - [ ] usprawnić ładowanie i zarządzanie fixturkami (\App\Command\SeedDatabaseCommand), np.
      użyć https://packagist.org/packages/nelmio/alice
    - [ ] przerobić \App\Command\SeedDatabaseCommand na invokable command (albo usunąć całkowicie po zaimplementowaniu
      powyższego)
    - [x] oznaczyć wszystkie routes poprawnymi metodami HTTP
    - [ ] użyj Symfony Forms na stronie profilowej
    - [ ] można być użyć Doctrine Param Convertera do pobierania od razu encji w argumentach akcji kontrolerów
    - [ ] dodać logowanie istotnych akcji w aplikacji (monolog)
    - [ ] naprawa warningów zwracanych przez testy z phoenix app
    - [x] optymalizacja zapytania db w homepage (n+1 issue)
    - [x] dodać .editorconfig
    - [ ] użycie join w repozytoriach gdzie ich brakuje (np. 2 razy where)
- [x] Zadanie 2 - Dodaj funkcjonalność importu zdjęć do SymfonyApp z PhoenixApi
- [x] Zadanie 3 - Filtrowanie zdjęć na stronie głównej
- [ ] Zadanie 4 - Zaimplementuj rate-limiting w aplikacji PhoenixApi

## Opis użycia AI

Używam AI (claude code) w iteracyjny sposób rozwiązując każde zadanie/problem indywidualnie (czyszcząc poprzednio
context agenta).  
Staram się używać podejścia SDD (nie vibe coding), mając pełną kontrolę nad oczekiwanym rezultatem, jak również
każdorazowo sprawdzając wygenerowane przez AI zmiany.  
Na początku każdego zadania/kroku używam "planning mode" aby zbudować kontekst agenta dla danego zadania oraz po
iteracyjnym poprawianiu planu (kiedy uznam że jest on wystarczająco dobry i uwzględnia wszystkie wymagania), przechodzę
do jego implementacji przez agenta.  
Staram się zawsze dostarczyć mu możliwie najbardziej konkretne wymagania i (swój) oczekiwany wynik, bez pola na
domysły.  
Również korzystam z jego rad i dyskutuję na ich temat w elementach gdzie coś można zaimplementować na kilka różnych
sposobów, aby wybrać najlepsze rozwiązanie.  
Po zaimplementowaniu kodu i jego przeglądzie, ew. dokonuje drobnych poprawek.
Następnie ręcznie commituje zmiany.  
Na końcu, używam jeszcze AI do szerszej analizy kodu/projektu co mógłbym jeszcze usprawnić, a o czym sam nie pomyślałem.

## Podsumowanie / rzeczy ogólne

Na początku zrobiłem review projektu i spisałem ten dokument jako plan na wykonanie zadania/zadań.  
Zaznaczę że dawno nie robiłem projektu w MVC, więć ma pewno znalazły by się tutaj rzeczy do poprawy.  
Gdybym miał jeszcze więcej czasu, na pewno zaadresowałbym wszystkie spisane a niedokończone punkty z tego dokumentu.
