## Założenia

- Skoro samo zadanie oraz README.md jest napisane w języku polskim, to ten dokument też napiszę w tym języku.

## Architektura

Jest to dosyć mały prosty/projekt z użyciem architektury MVC.  
Mając na uwadze ograniczony czas oraz ilość poprawek/zadań do wykonania pozostanę przy tej architekturze. 
Natomiast miejscami jest ona niespójna, co postaram się usprawnić oraz zwiększyć powtarzalność rozwiązań.
Mając więcej czasu pokusiłbym się o skorzystanie z architektury hexagonalnej (oraz być może warstwy z domeną), gdyż zauważyłem pewne elementy biznesowe jak:
 - dane i zależności: zdjęcia, polubienia, użytkownik
 - logikę/akcje: polub/"odlub" :P zdjęcie, import zdjęć

Natomiast zdaję sobię sprawę że dla projektu tej skali jest to lekki "overengineering" :P.

- [ ] niespójne podejście z Like component (wydzielony osobno) a reszta encji/repo wydzielona bardziej technicznie
- [ ] nie powinniśmy przekazywać do widoków bezpośrednio modeli z bazy. Albo przekazujemy wartości skalarne, albo tworzymy obiekty DTO jako warstwa pośrednia

## Testy

- [x] funkcjonalne
- [ ] integracyjne
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
    - [ ] pozbycie się \App\Likes\LikeRepository::setUser oraz stanowego property $user, jawne użycie user w zależnych metodach jako argument
    - [ ] użycie timestampable w \App\Likes\Like::$createdAt
    - [ ] usunąć \App\Entity\Photo::$likeCounter oraz opierać "counter" na relacjach do like - mamy wtedy spójność danych niezależnie od sposobu uaktualniania counter'a, usunąć LikeService (nie będzie potrzebny)
    - [ ] czy ten user może likować to samo zjęcie wielokrotnie? - powinno być to zabezpieczone
    - [ ] ustawić firewall na akcje/endpointy publiczne i dostępne po zalogowaniu
    - [ ] użyć #[Template] atrybutów pod widoki
    - [ ] w kontrolerach wstrzykiwać bezpośrednio konkretne repozytoria zamiast poprzez EntityManager
    - [ ] \App\Controller\ProfileController::profile pobrać id usera z sesji bezpośrednio a nie poprzez Request
    - [ ] stworzyć helper metodę? do pobrania zalogowanego usera w kontrolerach
    - [x] stworzyć AuthTokenRepository oraz UserRepository i wynieść zapytania SQL z \App\Controller\AuthController::login do nich, zaadresować SQJ injection
    - [ ] użyć mechanizmów Symfony do pokrycia endpointów autoryzacji z \App\Controller\AuthController
    - [ ] obsłużyć porzucone biblioteki raportowane przez composera
    - [ ] usprawnić ładowanie i zarządzanie fixturkami (\App\Command\SeedDatabaseCommand), np. użyć https://packagist.org/packages/nelmio/alice
    - [ ] przerobić \App\Command\SeedDatabaseCommand na invokable command (albo usunąć całkowicie po zaimplementowaniu powyższego)
    - [ ] oznaczyć wszystkie routes poprawnymi metodami HTTP
    - [ ] użyj Symfony Forms na stronie profilowej
    - [ ] dodać logowanie istotnych akcji w aplikacji (monolog)
- [ ] Zadanie 2 - Dodaj funkcjonalność importu zdjęć do SymfonyApp z PhoenixApi
- [ ] Zadanie 3 - Filtrowanie zdjęć na stronie głównej
- [ ] Zadanie 4 - Zaimplementuj rate-limiting w aplikacji PhoenixApi

## Co jeszcze bym usprawnił mając więcej czasu

- Wszystkie niezaadresowane tutaj punkty

## Opis użycia AI

Po początkowym review projektu i spisaniu tego dokumentu, używam AI (claude code) w iteracyjny sposób rozwiązując każde zadanie/problem indywidualnie (czyszcząc poprzednio context agenta).  
Staram się używać podejścia SDD (nie vibe coding), mając pełną kontrolę nad oczekiwanym rezultatem, jak również każdorazowo sprawdzając wygenerowane przez AI zmiany.  
Na początku każdego zadania/kroku używam "planning mode" aby zbudować kontekst agenta dla danego zadania oraz po iteracyjnym poprawianiu planu (kiedy uznam że jest on wystarczająco dobry i uwzględnia wszystkie wymagania), przechodzę do jego implementacji przez agenta.  
Staram się zawsze dostarczyć mu możliwie najbardziej konkretne wymagania i (swój) oczekiwany wynik, bez pola na domysły.
Również korzystam z jego rad i dyskutuję na ich temat w elementach gdzie coś można zaimplementować na kilka różnych sposobów, aby wybrać najlepsze rozwiązanie. 
Po zaimplementowaniu kodu i jego przeglądzie, ew. dokonuje drobnych poprawek.
Następnie ręcznie commituje zmiany.  
Używam jeszcze AI do szerszej analizy kodu/projektu co mógłbym jeszcze usprawnić, a o czym sam nie pomyślałem.
