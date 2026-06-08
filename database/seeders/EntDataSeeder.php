<?php

namespace Database\Seeders;

use App\Enums\PartType;
use App\Enums\QuestionType;
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
            'nusqas' => ['1', '2', '3'],
        ],
        'История Казахстана' => [
            'topics' => ['Казахстан в древности', 'Казахское ханство', 'Казахстан в XIX веке', 'Советский период', 'Независимый Казахстан'],
            'nusqas' => ['1', '2', '3'],
        ],
        'Грамотность чтения' => [
            'topics' => ['Работа с текстом', 'Анализ информации', 'Интерпретация текста'],
            'nusqas' => ['1', '2'],
        ],
        'Математическая грамотность' => [
            'topics' => ['Практические задачи', 'Логика и рассуждения', 'Применение математики'],
            'nusqas' => ['1', '2'],
        ],
        'Физика' => [
            'topics' => ['Механика', 'Термодинамика', 'Электростатика', 'Электродинамика', 'Оптика', 'Квантовая физика'],
            'nusqas' => ['1', '2', '3'],
        ],
        'Химия' => [
            'topics' => ['Строение атома', 'Химические реакции', 'Неорганическая химия', 'Органическая химия', 'Растворы'],
            'nusqas' => ['1', '2', '3'],
        ],
        'Биология' => [
            'topics' => ['Клетка', 'Генетика', 'Эволюция', 'Анатомия человека', 'Экология'],
            'nusqas' => ['1', '2', '3'],
        ],
        'География' => [
            'topics' => ['Физическая география', 'Экономическая география', 'Природные зоны', 'Население и хозяйство'],
            'nusqas' => ['1', '2'],
        ],
        'Информатика' => [
            'topics' => ['Основы программирования', 'Алгоритмы', 'Базы данных', 'Компьютерные сети', 'ИКТ'],
            'nusqas' => ['1', '2', '3'],
        ],
        'Английский язык' => [
            'topics' => ['Грамматика', 'Лексика', 'Чтение', 'Аудирование'],
            'nusqas' => ['1', '2'],
        ],
    ];

    /**
     * Канонические квоты вопросов на нұсқа (зеркалят TestAssemblyService::MANDATORY_QUOTAS
     * и ::PROFILE_QUOTA), чтобы при сборке полного ЕНТ из этих нұсқа набирался полный
     * комплект заданий, а не «что нашлось». 'group' — целевое количество подвопросов
     * к контекстам (каждый контекст даёт 3 шт., см. QuestionFactory::group()).
     *
     * @var array<string, array<string, int>>
     */
    private const NUSQA_QUOTAS = [
        'История Казахстана' => ['one' => 20],
        'Грамотность чтения' => ['group' => 10],
        'Математическая грамотность' => ['one' => 10],
        'Физика' => ['one' => 25, 'group' => 5, 'match' => 5, 'multi' => 5],
        'Математика' => ['one' => 25, 'group' => 5, 'match' => 5, 'multi' => 5],
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

        $mandatoryTitles = ['История Казахстана', 'Грамотность чтения', 'Математическая грамотность'];

        $this->command->info('Seeding ENT subjects, parts, and questions...');
        foreach ($this->entSubjects as $subjectTitle => $config) {
            $subject = Subject::firstOrCreate(
                ['title' => $subjectTitle],
                [
                    'is_ent_subject' => true,
                    'is_mandatory' => in_array($subjectTitle, $mandatoryTitles),
                    'is_active' => true,
                ]
            );

            foreach ($config['topics'] as $topicTitle) {
                $topic = Part::firstOrCreate([
                    'subject_id' => $subject->id,
                    'title' => $topicTitle,
                    'type' => PartType::Topic,
                ]);

                if ($topic->wasRecentlyCreated) {
                    $this->seedQuestions($subject->id, $topic->id);
                }
            }

            foreach ($config['nusqas'] as $nusqaTitle) {
                $nusqa = Part::firstOrCreate([
                    'subject_id' => $subject->id,
                    'title' => $nusqaTitle,
                    'type' => PartType::Nusqa,
                ]);

                if ($quota = self::NUSQA_QUOTAS[$subjectTitle] ?? null) {
                    $this->topUpToQuota($subject->id, $nusqa->id, $quota);
                } elseif ($nusqa->wasRecentlyCreated) {
                    $this->seedQuestions($subject->id, $nusqa->id, nusqa: true);
                }
            }
        }

        $this->command->info('Done!');
    }

    /**
     * Дозаполняет нұсқа вопросами до канонической квоты по типам, не трогая уже
     * существующие (повторный запуск идемпотентен — лишнего не создаст).
     *
     * @param  array<string, int>  $quota
     */
    private function topUpToQuota(int $subjectId, int $partId, array $quota): void
    {
        $existing = Question::where('subject_id', $subjectId)
            ->where('part_id', $partId)
            ->withCount('details')
            ->get()
            ->groupBy(fn (Question $q) => $q->type->value);

        foreach ($quota as $type => $target) {
            if ($type === QuestionType::IS_GROUP->value) {
                $existingDetails = ($existing->get($type) ?? collect())->sum('details_count');
                $missingDetails = max(0, $target - $existingDetails);

                if ($missingDetails > 0) {
                    // Каждый контекст несёт 3 подвопроса (см. QuestionFactory::group())
                    Question::factory()->group($subjectId, $partId)
                        ->count((int) ceil($missingDetails / 3))
                        ->create();
                }

                continue;
            }

            $missing = max(0, $target - ($existing->get($type)?->count() ?? 0));

            if ($missing > 0) {
                match ($type) {
                    QuestionType::SELECT_ONE->value => Question::factory()->one($subjectId, $partId)->count($missing)->create(),
                    QuestionType::SELECT_MULTI->value => Question::factory()->multi($subjectId, $partId)->count($missing)->create(),
                    QuestionType::IS_MATCH->value => Question::factory()->match($subjectId, $partId)->count($missing)->create(),
                    default => null,
                };
            }
        }
    }

    private function seedQuestions(int $subjectId, int $partId, bool $nusqa = false): void
    {
        $count = $nusqa ? 20 : 10;

        // Смесь всех типов вопросов — group получает остаток, чтобы сумма всегда была равна $count
        $oneCount = (int) ($count * 0.5);
        $multiCount = (int) ($count * 0.2);
        $matchCount = (int) ($count * 0.15);
        $groupCount = $count - $oneCount - $multiCount - $matchCount;

        Question::factory()->one($subjectId, $partId)->count($oneCount)->create();
        Question::factory()->multi($subjectId, $partId)->count($multiCount)->create();
        Question::factory()->match($subjectId, $partId)->count($matchCount)->create();
        Question::factory()->group($subjectId, $partId)->count($groupCount)->create();
    }
}
