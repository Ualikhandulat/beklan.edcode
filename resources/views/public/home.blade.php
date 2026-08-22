@extends('layouts.landing')
@section('title', __('Добро пожаловать'))

@section('content')
<div style="position:relative; overflow:hidden; min-height:100vh;">

    {{-- ambient background --}}
    <div style="position:fixed; inset:0; z-index:0; pointer-events:none; background:
        radial-gradient(900px 600px at 80% 0%, rgba(242,153,74,0.20), transparent 60%),
        radial-gradient(760px 560px at 2% 30%, rgba(242,184,80,0.16), transparent 60%),
        radial-gradient(700px 700px at 95% 88%, rgba(242,153,74,0.12), transparent 60%);"></div>
    <div style="position:fixed; inset:0; z-index:0; pointer-events:none; opacity:.7;
        background-image:linear-gradient(rgba(26,20,16,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(26,20,16,0.035) 1px, transparent 1px);
        background-size:64px 64px; mask-image:radial-gradient(ellipse 90% 70% at 50% 0%, #000 30%, transparent 75%); -webkit-mask-image:radial-gradient(ellipse 90% 70% at 50% 0%, #000 30%, transparent 75%);"></div>

    <div style="position:relative; z-index:1;">

    {{-- ===================== NAV ===================== --}}
    <header style="position:sticky; top:0; z-index:50; backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); background:rgba(251,247,241,0.78); border-bottom:1px solid rgba(26,20,16,0.07);">
        <nav style="max-width:1200px; margin:0 auto; padding:14px clamp(18px,4vw,40px); display:flex; align-items:center; justify-content:space-between; gap:18px;">
            <a href="#top" style="display:flex; align-items:center; gap:13px;">
                <img src="{{ asset('images/logo.png') }}" alt="EdCode" style="width:52px; height:52px; border-radius:16px; object-fit:cover; display:block; border:2px solid #fff; box-shadow:0 6px 18px rgba(242,153,74,0.3);">
                <span style="font-weight:700; font-size:23px; letter-spacing:-0.02em; color:#1C150F;">EdCode</span>
            </a>
            <div style="display:flex; align-items:center; gap:10px;">
                <x-locale-switcher />
                <a href="{{ route('login') }}" class="lp-cta" style="padding:10px 20px; border-radius:11px; font-size:14px; font-weight:500; color:#fff; background:linear-gradient(140deg, var(--accent,#F2994A), #EC7E2E); box-shadow:0 8px 20px rgba(242,153,74,0.38);">{{ __('Войти') }}</a>
            </div>
        </nav>
    </header>

    <a id="top"></a>

    {{-- ===================== HERO ===================== --}}
    <section style="max-width:1200px; margin:0 auto; padding:clamp(48px,7vw,96px) clamp(18px,4vw,40px) clamp(40px,5vw,72px); display:flex; flex-wrap:wrap; align-items:center; gap:clamp(36px,5vw,64px);">
        <div style="flex:1 1 440px; min-width:300px;">
            <div style="display:inline-flex; align-items:center; gap:9px; padding:8px 15px; border-radius:999px; background:rgba(242,153,74,0.12); border:1px solid rgba(242,153,74,0.28); font-size:13px; color:#C26A1E; margin-bottom:26px;">
                <span style="width:7px; height:7px; border-radius:50%; background:var(--accent,#F2994A); box-shadow:0 0 12px var(--accent,#F2994A); animation:lp-blink 2.2s infinite;"></span>
                {{ __('Платформа онлайн-тестирования для школьников') }}
            </div>
            <h1 style="margin:0 0 22px; font-size:clamp(42px,7.2vw,84px); line-height:0.98; letter-spacing:-0.035em; font-weight:700; color:#1C150F;">
                {{ __('Учись.') }}<br>{{ __('Проверяй.') }}<br><span style="background:linear-gradient(110deg, var(--accent,#F2994A), #EC6F3D 75%); -webkit-background-clip:text; background-clip:text; color:transparent;">{{ __('Развивайся.') }}</span>
            </h1>
            <p style="margin:0 0 34px; max-width:520px; font-size:clamp(16px,1.7vw,19px); line-height:1.6; color:#6B6259;">
                {{ __('EdCode — платформа для прохождения тестов по школьным предметам и подготовки к ЕНТ. Получай доступ от преподавателя, проходи тесты в своём темпе и отслеживай свой прогресс.') }}
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:14px; margin-bottom:44px;">
                <a href="{{ route('login') }}" class="lp-lift" style="padding:15px 28px; border-radius:13px; font-size:16px; font-weight:500; color:#fff; background:linear-gradient(140deg, var(--accent,#F2994A), #EC7E2E); box-shadow:0 14px 30px rgba(242,153,74,0.4);">{{ __('Войти в систему') }} →</a>
                @if ($trialAccess)
                    <a href="#trial" class="lp-lift lp-btn-shine" style="display:inline-flex; align-items:center; gap:10px; padding:15px 26px; border-radius:13px; font-size:16px; font-weight:600; color:#3A2606; background:linear-gradient(140deg, #F2B850, #E59A2E); box-shadow:0 14px 30px rgba(229,154,46,0.42);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        {{ __('Пробный тест бесплатно') }}
                    </a>
                @endif
                <a href="#how" class="lp-outline" style="padding:15px 26px; border-radius:13px; font-size:16px; font-weight:500; color:#1C150F; background:#fff; border:1px solid rgba(26,20,16,0.12); box-shadow:0 4px 14px rgba(80,50,20,0.06);">{{ __('Как это работает') }}</a>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:clamp(20px,4vw,44px);">
                <div>
                    <div style="font-size:clamp(26px,3vw,34px); font-weight:700; letter-spacing:-0.02em; color:#1C150F;">{{ $stats['students'] }}<span style="color:var(--accent,#F2994A);">+</span></div>
                    <div style="font-size:13px; color:#98908A;">{{ __('учеников') }}</div>
                </div>
                <div style="width:1px; align-self:stretch; background:rgba(26,20,16,0.10);"></div>
                <div>
                    <div style="font-size:clamp(26px,3vw,34px); font-weight:700; letter-spacing:-0.02em; color:#1C150F;">{{ $stats['subjects'] }}</div>
                    <div style="font-size:13px; color:#98908A;">{{ __('предметов') }}</div>
                </div>
                <div style="width:1px; align-self:stretch; background:rgba(26,20,16,0.10);"></div>
                <div>
                    <div style="font-size:clamp(26px,3vw,34px); font-weight:700; letter-spacing:-0.02em; color:#E0982F;">{{ $stats['groups'] }}</div>
                    <div style="font-size:13px; color:#98908A;">{{ __('групп') }}</div>
                </div>
            </div>
        </div>

        {{-- 3D mockup --}}
        <div style="flex:1 1 380px; min-width:300px; display:flex; justify-content:center;">
            <div style="position:relative; perspective:1500px; width:min(100%,440px);">
                <div style="position:absolute; inset:-40px; z-index:0; background:radial-gradient(closest-side, rgba(242,153,74,0.28), transparent 70%); filter:blur(20px); animation:lp-glow 4s ease-in-out infinite;"></div>

                <div style="position:relative; z-index:2; transform-style:preserve-3d; transform:rotateY(-15deg) rotateX(8deg); animation:lp-floatA 7s ease-in-out infinite;
                    background:#FFFFFF; border:1px solid rgba(26,20,16,0.07); border-radius:26px; padding:22px; box-shadow:0 50px 90px -28px rgba(120,70,20,0.45), 0 10px 24px rgba(0,0,0,0.06);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
                        <div style="display:flex; align-items:center; gap:9px;">
                            <span style="width:30px; height:30px; border-radius:9px; background:rgba(242,153,74,0.14); color:var(--accent,#F2994A); display:inline-flex; align-items:center; justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 7L5 12l4 5"/><path d="M15 7l4 5-4 5"/></svg></span>
                            <div>
                                <div style="font-size:14px; font-weight:500; color:#1C150F;">{{ __('Информатика') }}</div>
                                <div style="font-size:11px; color:#98908A;">{{ __('Формат ЕНТ') }}</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:7px; padding:7px 12px; border-radius:9px; background:rgba(242,153,74,0.12); border:1px solid rgba(242,153,74,0.3);">
                            <span style="width:6px; height:6px; border-radius:50%; background:#E0982F; animation:lp-blink 1.1s infinite;"></span>
                            <span style="font-family:'Ubuntu Mono',monospace; font-size:13px; font-weight:700; color:#C26A1E;">12:45</span>
                        </div>
                    </div>
                    <div style="height:6px; border-radius:99px; background:rgba(26,20,16,0.07); overflow:hidden; margin-bottom:8px;">
                        <div style="width:60%; height:100%; border-radius:99px; background:linear-gradient(90deg, var(--accent,#F2994A), #F2B850);"></div>
                    </div>
                    <div style="font-size:11px; color:#98908A; margin-bottom:18px;">6 / 10 {{ __('вопросов') }}</div>
                    <div style="font-size:15px; font-weight:500; line-height:1.45; margin-bottom:16px; color:#1C150F;">{{ __('Каждый шаг алгоритма точно и однозначно определён — какое это свойство?') }}</div>
                    <div style="display:flex; flex-direction:column; gap:9px;">
                        <div style="display:flex; align-items:center; gap:11px; padding:12px 13px; border-radius:12px; background:#FAF7F2; border:1px solid rgba(26,20,16,0.07);">
                            <span style="width:22px; height:22px; border-radius:7px; display:grid; place-items:center; background:rgba(26,20,16,0.05); font-size:12px; font-weight:700; color:#98908A;">A</span>
                            <span style="font-size:14px; color:#6B6259;">{{ __('Дискретность') }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:11px; padding:12px 13px; border-radius:12px; background:rgba(242,153,74,0.12); border:1px solid var(--accent,#F2994A); box-shadow:0 0 0 3px rgba(242,153,74,0.12);">
                            <span style="width:22px; height:22px; border-radius:7px; display:grid; place-items:center; background:var(--accent,#F2994A); font-size:12px; font-weight:700; color:#fff;">B</span>
                            <span style="font-size:14px; color:#1C150F; font-weight:500;">{{ __('Определённость') }}</span>
                            <span style="margin-left:auto; color:var(--accent,#F2994A); display:inline-flex;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.2 4.2L19 7"/></svg></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:11px; padding:12px 13px; border-radius:12px; background:#FAF7F2; border:1px solid rgba(26,20,16,0.07);">
                            <span style="width:22px; height:22px; border-radius:7px; display:grid; place-items:center; background:rgba(26,20,16,0.05); font-size:12px; font-weight:700; color:#98908A;">C</span>
                            <span style="font-size:14px; color:#6B6259;">{{ __('Массовость') }}</span>
                        </div>
                    </div>
                    <button style="margin-top:18px; width:100%; padding:13px; border:none; border-radius:12px; font-family:inherit; font-size:14px; font-weight:500; color:#fff; cursor:pointer; background:linear-gradient(140deg, var(--accent,#F2994A), #EC7E2E);">{{ __('Следующий вопрос') }} →</button>
                </div>

                <div style="position:absolute; z-index:3; bottom:54px; left:-30px; animation:lp-floatC 6.4s ease-in-out infinite; display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:13px; background:#fff; border:1px solid rgba(47,158,107,0.35); box-shadow:0 16px 30px rgba(80,50,20,0.12);">
                    <span style="width:20px; height:20px; border-radius:50%; display:grid; place-items:center; background:#2F9E6B; color:#fff;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.2 4.2L19 7"/></svg></span>
                    <span style="font-size:13px; font-weight:500; color:#1F7A52;">{{ __('Верный ответ') }}</span>
                </div>
                <div style="position:absolute; z-index:1; bottom:-18px; right:18px; animation:lp-floatD 5.8s ease-in-out infinite; padding:9px 14px; border-radius:12px; background:#fff; border:1px solid rgba(26,20,16,0.08); box-shadow:0 12px 24px rgba(80,50,20,0.1); font-size:12px; color:#6B6259;">10 000+ {{ __('подписчиков') }}</div>
            </div>
        </div>
    </section>

    {{-- ===================== TRIAL TEST ===================== --}}
    @if ($trialAccess)
    <section id="trial" style="max-width:1200px; margin:0 auto; padding:clamp(24px,4vw,48px) clamp(18px,4vw,40px);">
        <div style="position:relative; overflow:hidden; border-radius:28px; padding:clamp(36px,5vw,64px) clamp(24px,5vw,56px); background:linear-gradient(140deg, rgba(242,153,74,0.16), rgba(242,184,80,0.10)); border:1px solid rgba(242,153,74,0.35);">
            <div style="position:absolute; top:-90px; left:-50px; width:280px; height:280px; border-radius:50%; background:radial-gradient(closest-side, rgba(242,153,74,0.28), transparent 70%); animation:lp-drift 9s ease-in-out infinite;"></div>
            <div style="position:relative; z-index:1; display:flex; flex-wrap:wrap; align-items:center; gap:clamp(28px,4vw,52px);">
                <div style="flex:1 1 380px; min-width:280px;">
                    <div style="display:inline-flex; align-items:center; gap:8px; padding:7px 14px; border-radius:999px; background:#fff; border:1px solid rgba(242,153,74,0.4); font-size:13px; font-weight:700; color:#C26A1E; margin-bottom:18px;">
                        <span style="width:7px; height:7px; border-radius:50%; background:var(--accent,#F2994A); box-shadow:0 0 12px var(--accent,#F2994A); animation:lp-blink 2.2s infinite;"></span>
                        {{ __('Бесплатно') }}
                    </div>
                    <h2 style="margin:0 0 14px; font-size:clamp(28px,4.4vw,48px); line-height:1.06; letter-spacing:-0.03em; font-weight:700; color:#1C150F;">{{ __('Пройди пробный тест') }}</h2>
                    <p style="margin:0 0 26px; max-width:520px; font-size:clamp(15px,1.6vw,18px); line-height:1.6; color:#6B6259;">
                        {{ __('Зарегистрируйся и бесплатно пройди пробное тестирование — узнай формат вопросов и оцени свой уровень.') }}
                    </p>
                    <a href="{{ route('register') }}" class="lp-lift lp-btn-shine" style="display:inline-flex; align-items:center; gap:11px; padding:17px 34px; border-radius:14px; font-size:17px; font-weight:600; color:#fff; background:linear-gradient(140deg, var(--accent,#F2994A), #EC6F3D); box-shadow:0 18px 38px rgba(242,153,74,0.5);">
                        {{ __('Пройти пробный тест') }}
                        <svg class="lp-arrow" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <div style="margin-top:14px; font-size:13px; color:#98908A;">{{ __('Регистрация занимает меньше минуты') }}</div>
                </div>
                <div style="flex:1 1 260px; min-width:240px; display:flex; flex-direction:column; gap:12px;">
                    @if ($trialSubject)
                        <div style="display:flex; align-items:center; gap:12px; padding:16px 20px; border-radius:16px; background:#fff; border:1px solid rgba(242,153,74,0.3); box-shadow:0 10px 24px -14px rgba(120,70,20,0.25);">
                            <span style="width:38px; height:38px; flex:none; border-radius:11px; display:grid; place-items:center; background:var(--accent,#F2994A); color:#fff;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.5C12 5 10.4 4 8.4 4 6.4 4 5 5 4 5.5v13C5 18 6.4 17 8.4 17c2 0 3.6 1 3.6 2.5"/><path d="M12 6.5C12 5 13.6 4 15.6 4 17.6 4 19 5 20 5.5v13C19 18 17.6 17 15.6 17c-2 0-3.6 1-3.6 2.5"/></svg></span>
                            <div>
                                <div style="font-size:12px; color:#98908A;">{{ __('Предмет') }}</div>
                                <div style="font-size:16px; font-weight:700; color:#1C150F;">{{ $trialSubject->title }}</div>
                            </div>
                        </div>
                    @endif
                    <div style="display:flex; gap:12px;">
                        @if ($trialQuestionCount)
                            <div style="flex:1; padding:14px 18px; border-radius:16px; background:#fff; border:1px solid rgba(26,20,16,0.08);">
                                <div style="font-size:20px; font-weight:700; color:#1C150F;">{{ $trialQuestionCount }}</div>
                                <div style="font-size:12px; color:#98908A;">{{ __('вопросов') }}</div>
                            </div>
                        @endif
                        @if ($trialAccess->duration_minutes)
                            <div style="flex:1; padding:14px 18px; border-radius:16px; background:#fff; border:1px solid rgba(26,20,16,0.08);">
                                <div style="font-size:20px; font-weight:700; color:#1C150F;">{{ $trialAccess->duration_minutes }}</div>
                                <div style="font-size:12px; color:#98908A;">{{ __('минут') }}</div>
                            </div>
                        @endif
                        <div style="flex:1; padding:14px 18px; border-radius:16px; background:#fff; border:1px solid rgba(26,20,16,0.08);">
                            <div style="font-size:20px; font-weight:700; color:#E0982F;">{{ $trialAccess->attempts_limit > 0 ? $trialAccess->attempts_limit : '∞' }}</div>
                            <div style="font-size:12px; color:#98908A;">{{ __('попытка') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ===================== AUTHORS ===================== --}}
    <section id="authors" style="max-width:1200px; margin:0 auto; padding:clamp(40px,6vw,80px) clamp(18px,4vw,40px);">
        <div style="text-align:center; max-width:680px; margin:0 auto clamp(36px,5vw,56px);">
            <div style="display:inline-block; font-family:'Ubuntu Mono',monospace; font-size:12px; letter-spacing:0.24em; text-transform:uppercase; color:var(--accent,#F2994A); margin-bottom:14px;">{{ __('Авторы платформы') }}</div>
            <h2 style="margin:0 0 16px; font-size:clamp(28px,4.4vw,48px); line-height:1.06; letter-spacing:-0.03em; font-weight:700; color:#1C150F;">{{ __('Преподаватели, создавшие EdCode') }}</h2>
            <p style="margin:0; font-size:clamp(15px,1.6vw,18px); line-height:1.6; color:#6B6259;">{{ __('Платформу создали учителя информатики — победители республиканских и международных конкурсов, показавшие рекордный результат на ЕНТ.') }}</p>
        </div>

        <div style="display:flex; flex-direction:column; gap:24px;">

            {{-- Teacher 1 --}}
            <article style="display:flex; flex-wrap:wrap; gap:clamp(28px,3.5vw,52px); align-items:stretch; padding:clamp(20px,2.5vw,30px); border-radius:26px; background:linear-gradient(160deg, #FFFFFF, #FFFAF4); border:1px solid rgba(242,153,74,0.16); box-shadow:0 30px 60px -32px rgba(120,70,20,0.32);">
                <div style="flex:1 1 230px; max-width:300px; perspective:1300px;">
                    <div class="lp-tilt-l" style="position:relative; transform-style:preserve-3d; transform:rotateY(9deg) rotateX(3deg); transition:transform .6s cubic-bezier(.2,.7,.2,1); height:100%; min-height:332px;">
                        <div style="position:absolute; inset:0; border-radius:24px; border:2px solid rgba(242,153,74,0.55); transform:translateZ(-62px) translate(26px,24px);"></div>
                        <div style="position:absolute; inset:0; border-radius:24px; background:linear-gradient(160deg, rgba(242,184,80,0.35), rgba(242,153,74,0.12)); transform:translateZ(-34px) translate(-18px,16px);"></div>
                        <div style="position:relative; border-radius:22px; padding:4px; background:linear-gradient(160deg, #F2994A, #F2B850); box-shadow:0 40px 60px -22px rgba(180,95,25,0.5), 0 10px 22px rgba(0,0,0,0.08); height:100%;">
                            <img src="{{ asset('images/teacher-esenbekov.jpeg') }}" alt="Есенбеков Курбаналы" style="width:100%; height:100%; min-height:324px; object-fit:cover; object-position:center 12%; border-radius:18px; display:block;">
                        </div>
                        <div style="position:absolute; bottom:14px; left:14px; transform:translateZ(42px); padding:7px 13px; border-radius:10px; background:linear-gradient(140deg, #F2B850, #E59A2E); font-size:12px; font-weight:700; color:#3A2606; box-shadow:0 10px 22px rgba(180,120,30,0.35);">★ {{ __('Автор платформы') }}</div>
                    </div>
                </div>
                <div style="flex:3 1 360px; min-width:280px; display:flex; flex-direction:column;">
                    <h3 style="margin:0 0 6px; font-size:clamp(22px,2.6vw,30px); letter-spacing:-0.02em; font-weight:700; color:#1C150F;">Есенбеков Курбаналы Сералиевич</h3>
                    <div style="font-size:14px; color:var(--accent,#F2994A); margin-bottom:16px;">{{ __('Учитель информатики · Магистр педагогики · Педагог-эксперт') }}</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px;">
                        <span style="padding:6px 12px; border-radius:8px; font-size:12.5px; background:#FAF6F0; border:1px solid rgba(26,20,16,0.09); color:#6B6259;">{{ __('Стаж работы — 10 лет') }}</span>
                        <span style="padding:6px 12px; border-radius:8px; font-size:12.5px; background:#FAF6F0; border:1px solid rgba(26,20,16,0.09); color:#6B6259;">{{ __('В сфере ЕНТ — 5-й год') }}</span>
                        <span style="padding:6px 12px; border-radius:8px; font-size:12.5px; background:rgba(242,153,74,0.12); border:1px solid rgba(242,153,74,0.3); color:#C26A1E;">{{ __('Автор методических книг') }}</span>
                    </div>
                    <div style="font-family:'Ubuntu Mono',monospace; font-size:11px; letter-spacing:0.2em; text-transform:uppercase; color:#98908A; margin-bottom:13px;">{{ __('Достижения') }}</div>
                    <ul style="list-style:none; margin:0 0 22px; padding:0; display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:11px;">
                        @foreach ([
                            'Предметная олимпиада по информатике — районный этап, <b>I место</b>',
                            'Городской этап — <b>II место</b>',
                            'Республиканский конкурс «Зерде», городской этап — <b>II место</b>',
                            'ЕНТ-2025, рекордный балл по городу Шымкент — <b>138</b>',
                            'YouTube-канал — <b>10 000+</b> подписчиков, обучающие видео',
                            'Лучшие результаты на онлайн-платформе «Зерделі»',
                        ] as $achievement)
                            <li style="display:flex; gap:10px; align-items:flex-start;"><span style="flex:none; width:20px; height:20px; margin-top:1px; border-radius:6px; display:grid; place-items:center; background:rgba(242,153,74,0.14); color:#D97B2A;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.2 4.2L19 7"/></svg></span><span style="font-size:14px; line-height:1.45; color:#6B6259;">{!! __($achievement) !!}</span></li>
                        @endforeach
                    </ul>
                    <div style="margin-top:auto; display:flex; flex-wrap:wrap; gap:10px; padding-top:18px; border-top:1px solid rgba(26,20,16,0.08);">
                        <div style="flex:1 1 120px;"><div style="font-size:20px; font-weight:700; color:#E0982F;">138</div><div style="font-size:12px; color:#98908A;">{{ __('Рекорд ЕНТ') }}</div></div>
                        <div style="flex:1 1 120px;"><div style="font-size:20px; font-weight:700; color:#1C150F;">{{ __('10 лет') }}</div><div style="font-size:12px; color:#98908A;">{{ __('опыт') }}</div></div>
                        <div style="flex:1 1 120px;"><div style="font-size:20px; font-weight:700; color:#1C150F;">10K+</div><div style="font-size:12px; color:#98908A;">{{ __('подписчиков YouTube') }}</div></div>
                    </div>
                </div>
            </article>

            {{-- Teacher 2 --}}
            <article style="display:flex; flex-wrap:wrap; gap:clamp(28px,3.5vw,52px); align-items:stretch; padding:clamp(20px,2.5vw,30px); border-radius:26px; background:linear-gradient(160deg, #FFFFFF, #FFFAF4); border:1px solid rgba(242,153,74,0.16); box-shadow:0 30px 60px -32px rgba(120,70,20,0.32);">
                <div style="flex:1 1 230px; max-width:300px; perspective:1300px;">
                    <div class="lp-tilt-r" style="position:relative; transform-style:preserve-3d; transform:rotateY(-9deg) rotateX(3deg); transition:transform .6s cubic-bezier(.2,.7,.2,1); height:100%; min-height:332px;">
                        <div style="position:absolute; inset:0; border-radius:24px; border:2px solid rgba(242,153,74,0.55); transform:translateZ(-62px) translate(-26px,24px);"></div>
                        <div style="position:absolute; inset:0; border-radius:24px; background:linear-gradient(160deg, rgba(242,184,80,0.35), rgba(242,153,74,0.12)); transform:translateZ(-34px) translate(18px,16px);"></div>
                        <div style="position:relative; border-radius:22px; padding:4px; background:linear-gradient(160deg, #F2994A, #F2B850); box-shadow:0 40px 60px -22px rgba(180,95,25,0.5), 0 10px 22px rgba(0,0,0,0.08); height:100%;">
                            <img src="{{ asset('images/teacher-beklan.png') }}" alt="Тастанбек Беклан" style="width:100%; height:100%; min-height:324px; object-fit:cover; object-position:center 18%; border-radius:18px; display:block;">
                        </div>
                        <div style="position:absolute; bottom:14px; left:14px; transform:translateZ(42px); padding:7px 13px; border-radius:10px; background:linear-gradient(140deg, #F2B850, #E59A2E); font-size:12px; font-weight:700; color:#3A2606; box-shadow:0 10px 22px rgba(180,120,30,0.35);">★ {{ __('Автор платформы') }}</div>
                    </div>
                </div>
                <div style="flex:3 1 360px; min-width:280px; display:flex; flex-direction:column;">
                    <h3 style="margin:0 0 6px; font-size:clamp(22px,2.6vw,30px); letter-spacing:-0.02em; font-weight:700; color:#1C150F;">Тастанбек Беклан Момынбайұлы</h3>
                    <div style="font-size:14px; color:var(--accent,#F2994A); margin-bottom:16px;">{{ __('Учитель информатики · Магистр педагогики · Педагог-исследователь') }}</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px;">
                        <span style="padding:6px 12px; border-radius:8px; font-size:12.5px; background:#FAF6F0; border:1px solid rgba(26,20,16,0.09); color:#6B6259;">{{ __('Стаж работы — 12 лет') }}</span>
                        <span style="padding:6px 12px; border-radius:8px; font-size:12.5px; background:#FAF6F0; border:1px solid rgba(26,20,16,0.09); color:#6B6259;">{{ __('В сфере ЕНТ — 5-й год') }}</span>
                        <span style="padding:6px 12px; border-radius:8px; font-size:12.5px; background:rgba(242,153,74,0.12); border:1px solid rgba(242,153,74,0.3); color:#C26A1E;">{{ __('Автор методических книг') }}</span>
                    </div>
                    <div style="font-family:'Ubuntu Mono',monospace; font-size:11px; letter-spacing:0.2em; text-transform:uppercase; color:#98908A; margin-bottom:13px;">{{ __('Достижения') }}</div>
                    <ul style="list-style:none; margin:0 0 22px; padding:0; display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:11px;">
                        @foreach ([
                            'Призёр соревнований <b>международного уровня</b> по робототехнике',
                            'Робототехника бойынша Дубай, Дортмунд қалаларында өткен <b>Халықаралық деңгейдегі</b> жарыстардың жүлдегері',
                            'Предметная олимпиада по информатике, городской этап — <b>II место</b>',
                            'Автор <b>методических книг</b>',
                            'Лучшие результаты в центре подготовки к ЕНТ KEMENGER',
                        ] as $achievement)
                            <li style="display:flex; gap:10px; align-items:flex-start;"><span style="flex:none; width:20px; height:20px; margin-top:1px; border-radius:6px; display:grid; place-items:center; background:rgba(242,153,74,0.14); color:#D97B2A;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.2 4.2L19 7"/></svg></span><span style="font-size:14px; line-height:1.45; color:#6B6259;">{!! __($achievement) !!}</span></li>
                        @endforeach
                    </ul>
                    <div style="margin-top:auto; display:flex; flex-wrap:wrap; gap:10px; padding-top:18px; border-top:1px solid rgba(26,20,16,0.08);">
                        <div style="flex:1 1 120px;"><div style="font-size:20px; font-weight:700; color:#1C150F;">{{ __('12 лет') }}</div><div style="font-size:12px; color:#98908A;">{{ __('опыт') }}</div></div>
                        <div style="flex:1 1 120px;"><div style="font-size:20px; font-weight:700; color:#1C150F;">{{ __('Международный') }}</div><div style="font-size:12px; color:#98908A;">{{ __('призёр') }}</div></div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    {{-- ===================== FEATURES ===================== --}}
    <section id="features" style="max-width:1200px; margin:0 auto; padding:clamp(40px,6vw,80px) clamp(18px,4vw,40px);">
        <div style="max-width:680px; margin:0 auto clamp(36px,5vw,52px); text-align:center;">
            <div style="font-family:'Ubuntu Mono',monospace; font-size:12px; letter-spacing:0.24em; text-transform:uppercase; color:var(--accent,#F2994A); margin-bottom:14px;">{{ __('Возможности') }}</div>
            <h2 style="margin:0 0 16px; font-size:clamp(28px,4.4vw,48px); line-height:1.06; letter-spacing:-0.03em; font-weight:700; color:#1C150F;">{{ __('Всё для удобной подготовки') }}</h2>
            <p style="margin:0; font-size:clamp(15px,1.6vw,18px); line-height:1.6; color:#6B6259;">{{ __('Один аккаунт — доступ к тестам, прогрессу и истории прохождений.') }}</p>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px;">
            @php
                $features = [
                    ['bg' => 'rgba(242,153,74,0.13)', 'color' => 'var(--accent,#F2994A)', 'svg' => '<path d="M12 6.5C12 5 10.4 4 8.4 4 6.4 4 5 5 4 5.5v13C5 18 6.4 17 8.4 17c2 0 3.6 1 3.6 2.5"/><path d="M12 6.5C12 5 13.6 4 15.6 4 17.6 4 19 5 20 5.5v13C19 18 17.6 17 15.6 17c-2 0-3.6 1-3.6 2.5"/>', 'title' => 'Разнообразные предметы', 'text' => 'Математика, физика, история и другие школьные дисциплины и предметы ЕНТ — в одном месте.'],
                    ['bg' => 'rgba(47,158,107,0.14)', 'color' => '#2F9E6B', 'svg' => '<path d="M4 4v15a1 1 0 0 0 1 1h15"/><path d="M7.5 14l3.2-3.8 3 2.4L19 7"/><path d="M16 7h3v3"/>', 'title' => 'Отслеживание прогресса', 'text' => 'Мгновенные результаты по каждому тесту и полная история прохождений с баллами по темам.'],
                    ['bg' => 'rgba(224,152,47,0.15)', 'color' => '#E0982F', 'svg' => '<circle cx="12" cy="13.5" r="7.5"/><path d="M12 9.5v4l2.6 1.6"/><path d="M9 2.5h6"/>', 'title' => 'Тесты с таймером', 'text' => 'Прохождение в отведённое время — как в реальном экзамене, с автоматической проверкой.'],
                    ['bg' => 'rgba(242,153,74,0.13)', 'color' => 'var(--accent,#F2994A)', 'svg' => '<path d="M12 3l7 2.5v5.6c0 4.4-3 7.4-7 8.9-4-1.5-7-4.5-7-8.9V5.5z"/><path d="M9 12l2 2 4-4.4"/>', 'title' => 'Надёжная система', 'text' => 'Доступ выдаётся преподавателем — никаких случайных попыток и потерянных результатов.'],
                    ['bg' => 'rgba(47,158,107,0.14)', 'color' => '#2F9E6B', 'svg' => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><path d="M15 5.2a3 3 0 0 1 0 5.6"/><path d="M17 14.4A5.5 5.5 0 0 1 20.5 19"/>', 'title' => 'Групповое обучение', 'text' => 'Преподаватель видит успеваемость всей группы и каждого ученика в отдельности.'],
                    ['bg' => 'rgba(224,152,47,0.15)', 'color' => '#E0982F', 'svg' => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1.2"/>', 'title' => 'Подготовка к ЕНТ', 'text' => 'Отдельный формат тестов по предметам ЕНТ — отрабатывай реальный формат экзамена.'],
                ];
            @endphp
            @foreach ($features as $feature)
                <div class="lp-feature" style="padding:26px; border-radius:20px; background:#fff; border:1px solid rgba(26,20,16,0.07); box-shadow:0 14px 30px -18px rgba(80,50,20,0.16);">
                    <div style="width:46px; height:46px; border-radius:13px; display:grid; place-items:center; background:{{ $feature['bg'] }}; color:{{ $feature['color'] }}; margin-bottom:18px;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $feature['svg'] !!}</svg></div>
                    <h3 style="margin:0 0 9px; font-size:19px; font-weight:500; color:#1C150F;">{{ __($feature['title']) }}</h3>
                    <p style="margin:0; font-size:14.5px; line-height:1.55; color:#6B6259;">{{ __($feature['text']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== HOW IT WORKS ===================== --}}
    <section id="how" style="max-width:1200px; margin:0 auto; padding:clamp(40px,6vw,80px) clamp(18px,4vw,40px);">
        <div style="max-width:680px; margin:0 auto clamp(36px,5vw,52px); text-align:center;">
            <div style="font-family:'Ubuntu Mono',monospace; font-size:12px; letter-spacing:0.24em; text-transform:uppercase; color:var(--accent,#F2994A); margin-bottom:14px;">{{ __('Процесс') }}</div>
            <h2 style="margin:0 0 16px; font-size:clamp(28px,4.4vw,48px); line-height:1.06; letter-spacing:-0.03em; font-weight:700; color:#1C150F;">{{ __('Как это работает') }}</h2>
            <p style="margin:0; font-size:clamp(15px,1.6vw,18px); line-height:1.6; color:#6B6259;">{{ __('Три простых шага от получения доступа до результата.') }}</p>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px;">
            @php
                $steps = [
                    ['num' => '01', 'stroke' => 'rgba(242,153,74,0.55)', 'title' => 'Получи доступ', 'text' => 'Преподаватель назначает тебе или твоей группе доступ к нужным предметам и тестам.'],
                    ['num' => '02', 'stroke' => 'rgba(47,158,107,0.5)', 'title' => 'Пройди тест', 'text' => 'Отвечай на вопросы в удобное время, в рамках лимита по времени, как на реальном экзамене.'],
                    ['num' => '03', 'stroke' => 'rgba(224,152,47,0.6)', 'title' => 'Узнай результат', 'text' => 'Сразу получай баллы по каждой теме и следи за своим прогрессом в личном кабинете.'],
                ];
            @endphp
            @foreach ($steps as $step)
                <div style="position:relative; padding:30px 26px; border-radius:20px; background:#fff; border:1px solid rgba(26,20,16,0.07); box-shadow:0 14px 30px -18px rgba(80,50,20,0.16);">
                    <div style="font-family:'Ubuntu Mono',monospace; font-size:56px; font-weight:700; line-height:1; color:transparent; -webkit-text-stroke:1.5px {{ $step['stroke'] }}; margin-bottom:16px;">{{ $step['num'] }}</div>
                    <h3 style="margin:0 0 9px; font-size:20px; font-weight:500; color:#1C150F;">{{ __($step['title']) }}</h3>
                    <p style="margin:0; font-size:14.5px; line-height:1.55; color:#6B6259;">{{ __($step['text']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== SUBJECTS ===================== --}}
    <section id="subjects" style="max-width:1200px; margin:0 auto; padding:clamp(40px,6vw,80px) clamp(18px,4vw,40px);">
        <div style="max-width:680px; margin:0 auto clamp(32px,4vw,44px); text-align:center;">
            <div style="font-family:'Ubuntu Mono',monospace; font-size:12px; letter-spacing:0.24em; text-transform:uppercase; color:var(--accent,#F2994A); margin-bottom:14px;">{{ __('Предметы') }}</div>
            <h2 style="margin:0 0 16px; font-size:clamp(28px,4.4vw,48px); line-height:1.06; letter-spacing:-0.03em; font-weight:700; color:#1C150F;">{{ __('Чему можно учиться') }}</h2>
            <p style="margin:0; font-size:clamp(15px,1.6vw,18px); line-height:1.6; color:#6B6259;">{{ __('Школьные дисциплины и предметы для подготовки к ЕНТ.') }}</p>
        </div>
        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:12px;">
            @forelse ($subjects as $subject)
                <div style="display:flex; align-items:center; gap:11px; padding:14px 22px; border-radius:14px; background:linear-gradient(140deg, rgba(242,153,74,0.16), rgba(242,184,80,0.1)); border:1px solid rgba(242,153,74,0.4);">
                    <span style="width:34px; height:34px; flex:none; border-radius:9px; display:grid; place-items:center; background:var(--accent,#F2994A); color:#fff;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.5C12 5 10.4 4 8.4 4 6.4 4 5 5 4 5.5v13C5 18 6.4 17 8.4 17c2 0 3.6 1 3.6 2.5"/><path d="M12 6.5C12 5 13.6 4 15.6 4 17.6 4 19 5 20 5.5v13C19 18 17.6 17 15.6 17c-2 0-3.6 1-3.6 2.5"/></svg></span>
                    <div style="font-size:16px; font-weight:500; color:#1C150F;">{{ $subject->title }}</div>
                </div>
            @empty
                <div style="display:flex; align-items:center; gap:10px; padding:14px 20px; border-radius:14px; background:#fff; border:1px solid rgba(26,20,16,0.08); color:#98908A;">
                    <span style="display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.5C12 5 10.4 4 8.4 4 6.4 4 5 5 4 5.5v13C5 18 6.4 17 8.4 17c2 0 3.6 1 3.6 2.5"/><path d="M12 6.5C12 5 13.6 4 15.6 4 17.6 4 19 5 20 5.5v13C19 18 17.6 17 15.6 17c-2 0-3.6 1-3.6 2.5"/></svg></span>
                    {{ __('Скоро') }}
                </div>
            @endforelse
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section style="max-width:1200px; margin:0 auto; padding:clamp(24px,4vw,48px) clamp(18px,4vw,40px) clamp(56px,7vw,96px);">
        <div style="position:relative; overflow:hidden; border-radius:28px; padding:clamp(40px,6vw,76px) clamp(24px,5vw,56px); text-align:center; background:linear-gradient(140deg, rgba(242,153,74,0.16), rgba(242,184,80,0.12)); border:1px solid rgba(242,153,74,0.3);">
            <div style="position:absolute; top:-80px; right:-40px; width:260px; height:260px; border-radius:50%; background:radial-gradient(closest-side, rgba(242,153,74,0.3), transparent 70%); animation:lp-drift 9s ease-in-out infinite;"></div>
            <div style="position:relative; z-index:1;">
                <h2 style="margin:0 auto 16px; max-width:640px; font-size:clamp(28px,4.6vw,52px); line-height:1.05; letter-spacing:-0.03em; font-weight:700; color:#1C150F;">{{ __('Готов проверить свои знания?') }}</h2>
                <p style="margin:0 auto 32px; max-width:520px; font-size:clamp(15px,1.7vw,18px); line-height:1.6; color:#6B6259;">{{ __('Войди в систему по логину и паролю, который выдал преподаватель, и приступай к тестам.') }}</p>
                <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:14px;">
                    <a href="{{ route('login') }}" class="lp-lift" style="display:inline-block; padding:16px 36px; border-radius:14px; font-size:17px; font-weight:500; color:#fff; background:linear-gradient(140deg, var(--accent,#F2994A), #EC7E2E); box-shadow:0 16px 34px rgba(242,153,74,0.45);">{{ __('Войти в систему') }} →</a>
                    @if ($trialAccess)
                        <a href="{{ route('register') }}" class="lp-lift lp-btn-shine" style="display:inline-flex; align-items:center; gap:10px; padding:16px 32px; border-radius:14px; font-size:17px; font-weight:600; color:#3A2606; background:linear-gradient(140deg, #F2B850, #E59A2E); box-shadow:0 16px 34px rgba(229,154,46,0.45);">
                            {{ __('Пройти пробный тест') }}
                            <svg class="lp-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FOOTER ===================== --}}
    <footer style="border-top:1px solid rgba(26,20,16,0.08); background:linear-gradient(180deg, rgba(242,153,74,0.04), rgba(242,153,74,0.0));">
        <div style="max-width:1200px; margin:0 auto; padding:clamp(40px,5vw,64px) clamp(18px,4vw,40px) 30px; display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:36px;">
            <div style="flex:1 1 320px; min-width:260px;">
                <a href="#top" style="display:inline-flex; align-items:center; gap:16px; margin-bottom:18px;">
                    <img src="{{ asset('images/logo.png') }}" alt="EdCode" style="width:76px; height:76px; border-radius:22px; object-fit:cover; display:block; border:2px solid #fff; box-shadow:0 12px 30px rgba(242,153,74,0.32);">
                    <span style="font-weight:700; font-size:32px; letter-spacing:-0.02em; color:#1C150F;">EdCode</span>
                </a>
                <p style="margin:0; max-width:340px; font-size:14.5px; line-height:1.6; color:#6B6259;">{{ __('Платформа онлайн-тестирования и подготовки к ЕНТ для школьников.') }}</p>
            </div>

            <div style="flex:1 1 240px; min-width:220px;">
                <div style="font-family:'Ubuntu Mono',monospace; font-size:11px; letter-spacing:0.2em; text-transform:uppercase; color:#98908A; margin-bottom:16px;">{{ __('Контакты') }}</div>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <a href="tel:+77718861404" class="lp-link" style="display:inline-flex; align-items:center; gap:11px; font-size:17px; font-weight:500; color:#1C150F;">
                        <span style="width:38px; height:38px; flex:none; border-radius:11px; display:grid; place-items:center; background:rgba(242,153,74,0.13); color:var(--accent,#F2994A);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5.5 4h3l1.4 4.3-2 1.3a11 11 0 0 0 4.8 4.8l1.3-2 4.3 1.4v3a1.8 1.8 0 0 1-1.9 1.8A15.5 15.5 0 0 1 3.7 6.4 1.8 1.8 0 0 1 5.5 4z"/></svg></span>
                        +7 771 886 1404
                    </a>
                    <a href="tel:+77027280307" class="lp-link" style="display:inline-flex; align-items:center; gap:11px; font-size:17px; font-weight:500; color:#1C150F;">
                        <span style="width:38px; height:38px; flex:none; border-radius:11px; display:grid; place-items:center; background:rgba(242,153,74,0.13); color:var(--accent,#F2994A);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5.5 4h3l1.4 4.3-2 1.3a11 11 0 0 0 4.8 4.8l1.3-2 4.3 1.4v3a1.8 1.8 0 0 1-1.9 1.8A15.5 15.5 0 0 1 3.7 6.4 1.8 1.8 0 0 1 5.5 4z"/></svg></span>
                        +7 702 728 0307
                    </a>
                </div>
            </div>

            <div style="flex:0 1 auto;">
                <div style="font-family:'Ubuntu Mono',monospace; font-size:11px; letter-spacing:0.2em; text-transform:uppercase; color:#98908A; margin-bottom:16px;">{{ __('Ссылки') }}</div>
                <div style="display:flex; flex-direction:column; gap:11px; font-size:15px; color:#6B6259;">
                    <a href="#authors" class="lp-link">{{ __('Авторы') }}</a>
                    <a href="#features" class="lp-link">{{ __('Возможности') }}</a>
                    <a href="#how" class="lp-link">{{ __('Как это работает') }}</a>
                    <a href="{{ route('login') }}" class="lp-link">{{ __('Войти в систему') }}</a>
                </div>
            </div>
        </div>
        <div style="max-width:1200px; margin:0 auto; padding:20px clamp(18px,4vw,40px) 36px; border-top:1px solid rgba(26,20,16,0.07); font-size:13px; color:#98908A;">© {{ date('Y') }} EdCode. {{ __('Все права защищены.') }}</div>
    </footer>

    </div>
</div>
@endsection
