<?php

namespace Database\Seeders;

use App\Enums\PartType;
use App\Models\Group;
use App\Models\Part;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EntDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var array<string, array<string>> */
    private array $entSubjects = [
        'Математика' => [
            'topics' => ['Числа и вычисления', 'Алгебра', 'Геометрия', 'Статистика и теория вероятностей', 'Математический анализ'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2', 'Нұсқа 3'],
        ],
        'История Казахстана' => [
            'topics' => ['Казахстан в древности', 'Казахское ханство', 'Казахстан в XIX веке', 'Советский период', 'Независимый Казахстан'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2', 'Нұсқа 3'],
        ],
        'Грамотность чтения' => [
            'topics' => ['Работа с текстом', 'Анализ информации', 'Интерпретация текста'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2'],
        ],
        'Математическая грамотность' => [
            'topics' => ['Практические задачи', 'Логика и рассуждения', 'Применение математики'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2'],
        ],
        'Физика' => [
            'topics' => ['Механика', 'Термодинамика', 'Электростатика', 'Электродинамика', 'Оптика', 'Квантовая физика'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2', 'Нұсқа 3'],
        ],
        'Химия' => [
            'topics' => ['Строение атома', 'Химические реакции', 'Неорганическая химия', 'Органическая химия', 'Растворы'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2', 'Нұсқа 3'],
        ],
        'Биология' => [
            'topics' => ['Клетка', 'Генетика', 'Эволюция', 'Анатомия человека', 'Экология'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2', 'Нұсқа 3'],
        ],
        'География' => [
            'topics' => ['Физическая география', 'Экономическая география', 'Природные зоны', 'Население и хозяйство'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2'],
        ],
        'Информатика' => [
            'topics' => ['Основы программирования', 'Алгоритмы', 'Базы данных', 'Компьютерные сети', 'ИКТ'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2', 'Нұсқа 3'],
        ],
        'Английский язык' => [
            'topics' => ['Грамматика', 'Лексика', 'Чтение', 'Аудирование'],
            'nusqas' => ['Нұсқа 1', 'Нұсқа 2'],
        ],
    ];

    private array $groups = [
        ['title' => '10А — 2025', 'description' => 'Группа профильного обучения, физ-мат направление'],
        ['title' => '10Б — 2025', 'description' => 'Группа общеобразовательного обучения'],
        ['title' => '11А — 2025', 'description' => 'Подготовительная группа к ЕНТ, биол-хим направление'],
        ['title' => '11Б — 2025', 'description' => 'Подготовительная группа к ЕНТ, гуманитарное направление'],
        ['title' => '9А — 2025', 'description' => 'Группа базовой подготовки'],
    ];

    public function run(): void
    {
        $this->command->info('Seeding groups...');
        $groups = [];
        foreach ($this->groups as $g) {
            $groups[] = Group::firstOrCreate(['title' => $g['title']], $g);
        }

        $this->command->info('Seeding 100 students...');
        $existing = User::where('role', 'student')->count();
        $needed = max(0, 100 - $existing);
        $students = $needed > 0
            ? User::factory()->student()->count($needed)->create()
            : User::where('role', 'student')->get();

        // Distribute students across groups (leave ~10 without a group)
        $students->slice(0, 90)->chunk(18)->each(function ($chunk, $index) use ($groups) {
            $chunk->each(fn ($u) => $u->update(['group_id' => $groups[$index]->id]));
        });

        $this->command->info('Seeding ENT subjects, parts, and questions...');
        foreach ($this->entSubjects as $subjectTitle => $config) {
            $subject = Subject::firstOrCreate(
                ['title' => $subjectTitle],
                ['is_ent_subject' => true, 'is_active' => true]
            );

            foreach ($config['topics'] as $topicTitle) {
                $topic = Part::create([
                    'subject_id' => $subject->id,
                    'title' => $topicTitle,
                    'type' => PartType::Topic,
                ]);

                $this->seedQuestions($subject->id, $topic->id);
            }

            foreach ($config['nusqas'] as $nusqaTitle) {
                $nusqa = Part::create([
                    'subject_id' => $subject->id,
                    'title' => $nusqaTitle,
                    'type' => PartType::Nusqa,
                ]);

                $this->seedQuestions($subject->id, $nusqa->id, nusqa: true);
            }
        }

        $this->command->info('Done!');
    }

    private function seedQuestions(int $subjectId, int $partId, bool $nusqa = false): void
    {
        $count = $nusqa ? 20 : 10;

        // Mix of all question types
        $oneCount = (int) ($count * 0.5);
        $multiCount = (int) ($count * 0.2);
        $matchCount = (int) ($count * 0.15);
        $groupCount = (int) ($count * 0.15);

        Question::factory()->one($subjectId, $partId)->count($oneCount)->create();
        Question::factory()->multi($subjectId, $partId)->count($multiCount)->create();
        Question::factory()->match($subjectId, $partId)->count($matchCount)->create();
        Question::factory()->group($subjectId, $partId)->count($groupCount)->create();
    }
}
