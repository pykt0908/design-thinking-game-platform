<template>
  <div class="player-container position-relative">
    <div v-if="loading" class="d-flex flex-column align-center justify-center h-100 text-white">
      <v-progress-circular indeterminate size="48" color="primary" class="mb-4"></v-progress-circular>
      <div class="text-subtitle-1">กำลังโหลดเกม...</div>
    </div>

    <div v-else-if="error" class="d-flex flex-column align-center justify-center h-100 text-white pa-6 text-center">
      <v-icon icon="mdi-alert-circle-outline" size="64" color="error" class="mb-3"></v-icon>
      <div class="text-h6 font-weight-bold mb-2">{{ error }}</div>
      <v-btn color="primary" to="/dashboard" rounded="lg">กลับหน้าหลัก</v-btn>
    </div>

    <div v-else-if="schema" class="d-flex flex-column h-100">
      <!-- Player Top Bar per spec Section 30 -->
      <header class="player-header px-6 py-3 d-flex align-center justify-space-between text-white border-b">
        <div class="d-flex align-center">
          <v-btn icon="mdi-close" variant="text" size="small" color="white" class="mr-3" @click="confirmExit" title="ออกจากเกม"></v-btn>
          <div>
            <div class="font-weight-bold text-subtitle-1">{{ schema.title }}</div>
            <div class="text-caption text-grey-lighten-1">{{ currentScene?.title }}</div>
          </div>
        </div>

        <div class="d-flex align-center gap-3">
          <!-- Timer -->
          <v-chip color="slate-800" variant="flat" class="text-white font-weight-bold">
            <v-icon start icon="mdi-timer-outline" color="amber"></v-icon>
            {{ formatTime(timeRemaining) }}
          </v-chip>

          <!-- Score -->
          <v-chip color="amber-darken-2" variant="flat" class="text-white font-weight-bold">
            <v-icon start icon="mdi-star" color="white"></v-icon>
            {{ currentScore }} แต้ม
          </v-chip>
        </div>
      </header>

      <!-- Player Progress Bar -->
      <v-progress-linear
        :model-value="progressPercent"
        color="accent"
        height="4"
      ></v-progress-linear>

      <!-- Scene Runtime Canvas -->
      <main class="player-canvas flex-grow-1 d-flex flex-column justify-center align-center pa-4">
        <!-- Result / Completion Screen per spec Section 32 -->
        <div v-if="isGameComplete" class="victory-card text-center pa-8 rounded-2xl bg-white elevation-4 max-w-lg w-100 animate-fade-in">
          <v-avatar size="64" color="amber-lighten-4" class="mb-3">
            <v-icon icon="mdi-party-popper" color="warning" size="36"></v-icon>
          </v-avatar>
          <h2 class="text-h4 font-weight-bold text-slate-800 mb-1">ภารกิจสำเร็จ!</h2>
          <p class="text-body-2 text-grey mb-6">คุณได้ทำแบบทดสอบและเรียนรู้ผ่านเกมเรียบร้อยแล้ว</p>

          <div class="score-badge pa-6 rounded-2xl mb-6 bg-purple-lighten-5">
            <div class="text-h2 font-weight-bold text-primary mb-1">
              {{ currentScore }}
            </div>
            <div class="text-subtitle-2 text-grey">คะแนนเต็ม 100</div>

            <div class="stars mt-3 d-flex justify-center gap-1">
              <v-icon
                v-for="s in 3"
                :key="s"
                :icon="s <= calculateStars() ? 'mdi-star' : 'mdi-star-outline'"
                color="amber"
                size="32"
              ></v-icon>
            </div>
          </div>

          <div class="d-flex justify-space-around py-3 border-t border-b mb-6 text-slate-700">
            <div>
              <div class="text-caption text-grey">เวลาที่ใช้</div>
              <div class="font-weight-bold">{{ formatTime(durationSpent) }}</div>
            </div>
            <div>
              <div class="text-caption text-grey">ตอบถูก</div>
              <div class="font-weight-bold">{{ correctAnswersCount }} ข้อ</div>
            </div>
            <div>
              <div class="text-caption text-grey">ผลการประเมิน</div>
              <div class="font-weight-bold text-success d-flex align-center justify-center">
                <template v-if="currentScore >= (schema.scoring?.passingScore ?? 60)">
                  <v-icon icon="mdi-check-circle" size="16" class="mr-1"></v-icon> ผ่านเกณฑ์
                </template>
                <template v-else>
                  <v-icon icon="mdi-alert-circle" size="16" class="mr-1"></v-icon> ต้องปรับปรุง
                </template>
              </div>
            </div>
          </div>

          <div class="d-flex gap-3 justify-center">
            <v-btn variant="outlined" color="primary" rounded="lg" prepend-icon="mdi-reload" @click="restartGame">
              เล่นใหม่อีกครั้ง
            </v-btn>
            <v-btn color="primary" rounded="lg" to="/dashboard">
              กลับสู่บทเรียน
            </v-btn>
          </div>
        </div>

        <!-- Real HTML5 Sandboxed Game Runner -->
        <div v-else-if="isHtml5Game" class="html5-game-viewport w-100 h-100 d-flex flex-column align-center justify-center position-relative" style="min-height: 82vh;">
          <iframe
            :srcdoc="schema.bundle || schema.html"
            class="html5-game-iframe w-100 rounded-2xl elevation-4"
            sandbox="allow-scripts allow-same-origin allow-modals"
            allow="fullscreen; autoplay"
            style="width: 100%; height: 82vh; border: 2px solid rgba(198, 112, 255, 0.4); background: #000;"
          ></iframe>
        </div>

        <!-- Active Scene Interactive Content -->
        <div v-else-if="currentScene" class="scene-active-content w-100 max-w-xl d-flex flex-column align-center animate-fade-in">
          <!-- Elements inside current scene -->
          <template v-for="el in currentScene.elements" :key="el.id">
            <!-- 1. Dialogue / NPC Element -->
            <div v-if="el.type === 'character'" class="dialogue-box w-100 pa-6 rounded-2xl bg-white elevation-3 border-card">
              <div class="d-flex align-center mb-4">
                <v-avatar size="60" class="mr-4 elevation-2 bg-purple-lighten-5">
                  <v-img :src="el.avatar || 'https://api.dicebear.com/7.x/bottts/svg?seed=Teacher'"></v-img>
                </v-avatar>
                <div>
                  <div class="text-subtitle-1 font-weight-bold text-primary">{{ el.name || el.dialogue?.speaker }}</div>
                  <div class="text-caption text-grey">ผู้ให้คำแนะนำประจำภารกิจ</div>
                </div>
              </div>

              <div class="text-body-1 text-slate-800 line-height-relaxed mb-6 font-weight-medium">
                {{ el.dialogue?.text }}
              </div>

              <div class="d-flex justify-end">
                <v-btn
                  class="ai-gradient-bg text-white font-weight-bold px-6 py-2"
                  rounded="lg"
                  size="large"
                  @click="goToNextScene(el.dialogue?.nextScene)"
                >
                  {{ el.dialogue?.actionText || 'ไปยังด่านถัดไป' }} &rarr;
                </v-btn>
              </div>
            </div>

            <!-- 2. Question / Combat Element: RPG Turn-Based Battle Mode -->
            <div v-else-if="el.type === 'question' && isRpgMode" class="rpg-battle-arena w-100 max-w-2xl animate-fade-in">
              <!-- Combat Stage Header Banner -->
              <div class="rpg-stage-header d-flex justify-space-between align-center mb-4 px-4 py-2 rounded-xl">
                <div class="d-flex align-center gap-2">
                  <span class="battle-badge">⚔️ 2D RPG BATTLE</span>
                  <span class="text-caption text-grey-lighten-1 font-weight-bold">STAGE {{ currentSceneIndex }} / {{ schema?.scenes ? schema.scenes.length - 1 : 3 }}</span>
                </div>
                <div class="text-caption text-amber-accent-2 font-weight-bold d-flex align-center gap-1">
                  <v-icon icon="mdi-sword-cross" size="14"></v-icon>
                  <span>โหมดการต่อสู้ผลัดกันโจมตี (Turn-Based Combat)</span>
                </div>
              </div>

              <!-- Combat Battlefield (Hero vs Boss) -->
              <div class="battlefield-card pa-6 rounded-2xl mb-4 position-relative overflow-hidden">
                <div class="battle-grid-overlay"></div>

                <!-- Floating Combat Damage Text -->
                <transition name="damage-pop">
                  <div v-if="combatText" class="floating-damage-text" :class="combatEffectType">
                    {{ combatText }}
                  </div>
                </transition>

                <div class="d-flex justify-space-between align-center position-relative" style="z-index: 2;">
                  <!-- Hero Unit (Left) -->
                  <div class="fighter-unit hero-unit d-flex flex-column align-center" :class="{ 'hero-lunging': isHeroAttacking, 'unit-hit': isHeroHit }">
                    <div class="unit-avatar-wrapper position-relative mb-2">
                      <v-avatar size="84" class="elevation-6 hero-avatar-border">
                        <v-img src="https://api.dicebear.com/7.x/adventurer/svg?seed=FelixWarrior&backgroundColor=b6e3f4"></v-img>
                      </v-avatar>
                      <span class="unit-lvl-badge">Lv. 5</span>
                    </div>
                    <div class="unit-name font-weight-bold text-white mb-1">ผู้กล้าแห่งปัญญา</div>
                    <div class="unit-class text-caption text-cyan-accent-2 mb-2">Design Hero</div>

                    <!-- Hero HP Bar -->
                    <div class="hp-gauge-container w-100">
                      <div class="d-flex justify-space-between text-caption font-weight-bold mb-1">
                        <span class="text-green-accent-3">HERO HP</span>
                        <span class="text-white">{{ heroHp }}/100</span>
                      </div>
                      <div class="hp-track">
                        <div class="hp-fill hero-hp-fill" :style="{ width: `${Math.max(0, heroHp)}%` }"></div>
                      </div>
                    </div>

                    <!-- Hero MP Bar -->
                    <div class="mp-gauge-container w-100 mt-1">
                      <div class="d-flex justify-space-between text-caption font-weight-bold mb-1">
                        <span class="text-light-blue-accent-2">MP</span>
                        <span class="text-grey-lighten-2">80/100</span>
                      </div>
                      <div class="mp-track">
                        <div class="mp-fill" style="width: 80%;"></div>
                      </div>
                    </div>
                  </div>

                  <!-- Center Clash Crest -->
                  <div class="vs-clash-column d-flex flex-column align-center px-4">
                    <div class="vs-emblem mb-2">VS</div>
                    <div class="turn-indicator font-weight-bold text-caption text-uppercase px-3 py-1 rounded-pill">
                      ⚔️ เลือกวิชาโจมตี
                    </div>
                  </div>

                  <!-- Boss Unit (Right) -->
                  <div class="fighter-unit boss-unit d-flex flex-column align-center" :class="{ 'unit-hit': isBossHit }">
                    <div class="unit-avatar-wrapper position-relative mb-2">
                      <v-avatar size="84" class="elevation-6 boss-avatar-border">
                        <v-img :src="getBossAvatar(currentSceneIndex)"></v-img>
                      </v-avatar>
                      <span class="boss-lvl-badge">BOSS</span>
                    </div>
                    <div class="unit-name font-weight-bold text-white mb-1 text-center" style="max-width: 140px; font-size: 13px;">
                      {{ getBossName(currentSceneIndex) }}
                    </div>
                    <div class="unit-class text-caption text-red-accent-2 mb-2">Arch-Nemesis</div>

                    <!-- Boss HP Bar -->
                    <div class="hp-gauge-container w-100">
                      <div class="d-flex justify-space-between text-caption font-weight-bold mb-1">
                        <span class="text-red-accent-2">BOSS HP</span>
                        <span class="text-white">{{ bossHp }}/100</span>
                      </div>
                      <div class="hp-track">
                        <div class="hp-fill boss-hp-fill" :style="{ width: `${Math.max(0, bossHp)}%` }"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Boss Question / Battle Challenge -->
              <div class="rpg-question-scroll pa-5 rounded-2xl mb-4 elevation-3">
                <div class="d-flex align-center gap-2 mb-2 text-amber-lighten-3 font-weight-bold text-caption">
                  <v-icon icon="mdi-shield-sword" size="18" color="amber"></v-icon>
                  <span>คาถาคำถามท้าทาย (+{{ el.points || 30 }} EXP)</span>
                </div>
                <div class="rpg-question-title text-h6 font-weight-bold text-white mb-3">
                  {{ el.question }}
                </div>
                <div v-if="el.image" class="mb-3 text-center">
                  <img :src="el.image" class="rounded-xl border-card" style="max-height: 160px; max-width: 100%; object-fit: cover;" />
                </div>
              </div>

              <!-- RPG Action Command Spell Deck -->
              <div class="spell-actions-grid mb-4">
                <button
                  v-for="(opt, idx) in el.options"
                  :key="opt.id"
                  class="spell-command-btn text-left pa-4 rounded-xl d-flex align-center justify-space-between transition-all"
                  :class="getRpgOptionClasses(opt, el.id)"
                  :disabled="answeredQuestions[el.id] !== undefined"
                  @click="handleRpgSelectOption(el, opt)"
                >
                  <div class="d-flex align-center gap-3">
                    <div class="spell-icon-badge" :class="'spell-slot-' + (idx % 4)">
                      {{ ['⚔️', '⚡', '🛡️', '🔮'][idx % 4] }}
                    </div>
                    <div>
                      <div class="text-caption text-grey-lighten-1 font-weight-bold">
                        {{ ['วิชาฟาดฟันตรรกะ', 'สายฟ้าสังเคราะห์', 'เกราะทดสอบความจริง', 'มนตราปัญญาญาณ'][idx % 4] }}
                      </div>
                      <div class="text-body-2 font-weight-medium text-white">{{ opt.text }}</div>
                    </div>
                  </div>

                  <div class="d-flex align-center">
                    <v-icon
                      v-if="answeredQuestions[el.id] !== undefined && opt.isCorrect"
                      icon="mdi-check-circle"
                      color="success"
                      size="24"
                    ></v-icon>
                    <v-icon
                      v-else-if="selectedAnswers[el.id] === opt.id && !opt.isCorrect"
                      icon="mdi-close-circle"
                      color="error"
                      size="24"
                    ></v-icon>
                  </div>
                </button>
              </div>

              <!-- Combat Battle Feedback & Next Turn Button -->
              <div v-if="answeredQuestions[el.id]" class="combat-feedback-box pa-4 rounded-xl animate-fade-in" :class="answeredQuestions[el.id].is_correct ? 'feedback-victory' : 'feedback-defeat'">
                <div class="d-flex align-center font-weight-bold text-subtitle-2 mb-1" :class="answeredQuestions[el.id].is_correct ? 'text-green-accent-3' : 'text-amber-accent-2'">
                  <v-icon :icon="answeredQuestions[el.id].is_correct ? 'mdi-sword-cross' : 'mdi-shield-alert'" class="mr-2"></v-icon>
                  <span>{{ answeredQuestions[el.id].is_correct ? 'โจมตีสำเร็จ! คริติคอลโดนจุดอ่อนของศัตรู (+ ' + (el.points || 30) + ' แต้ม)' : 'ถูกบอสสะท้อนการโจมตี!' }}</span>
                </div>
                <div class="text-body-2 text-grey-lighten-2 mb-3">
                  {{ answeredQuestions[el.id].explanation || el.explanation || 'ศึกษาตรรกะนี้เพื่อเตรียมพร้อมรับมือการโจมตีถัดไป!' }}
                </div>

                <div class="d-flex justify-end">
                  <v-btn
                    class="ai-gradient-bg text-white font-weight-bold px-6"
                    rounded="lg"
                    size="default"
                    @click="goToNextRpgStage(el.nextScene)"
                  >
                    {{ currentSceneIndex < (schema?.scenes?.length || 1) - 1 ? 'รุกคืบสู่ด่านถัดไป ➔' : 'พิชิตบอสสำเร็จ ดูสรุปผล 🏆' }}
                  </v-btn>
                </div>
              </div>
            </div>

            <!-- Kahoot / Live Speed Quiz Mode -->
            <div v-else-if="el.type === 'question' && isKahootMode" class="kahoot-arena w-100 max-w-2xl animate-fade-in">
              <div class="kahoot-header d-flex justify-space-between align-center mb-4 px-4 py-3 rounded-2xl">
                <div class="d-flex align-center gap-2">
                  <span class="kahoot-live-badge">🔥 LIVE SPEED ARENA</span>
                  <span class="text-caption text-grey-lighten-1 font-weight-bold">ข้อ {{ currentSceneIndex }} / {{ schema?.scenes ? schema.scenes.length - 1 : 3 }}</span>
                </div>
                <div class="d-flex align-center gap-3">
                  <span class="text-caption text-amber-accent-2 font-weight-bold">⚡ SPEED BONUS</span>
                  <div class="kahoot-timer-pill px-3 py-1 rounded-pill">
                    ⏱️ {{ formatTime(timeRemaining) }}
                  </div>
                </div>
              </div>

              <!-- Question Billboard -->
              <div class="kahoot-question-billboard pa-6 rounded-2xl mb-4 text-center">
                <h2 class="text-h5 font-weight-black text-slate-900 mb-2">
                  {{ el.question }}
                </h2>
                <div v-if="el.image" class="mt-3 text-center">
                  <img :src="el.image" class="rounded-xl elevation-2" style="max-height: 180px; max-width: 100%; object-fit: cover;" />
                </div>
              </div>

              <!-- 4 Vibrant Color Blocks -->
              <div class="kahoot-grid mb-4">
                <button
                  v-for="(opt, idx) in el.options"
                  :key="opt.id"
                  class="kahoot-block-btn pa-5 rounded-2xl text-left d-flex align-center justify-space-between transition-all"
                  :class="['kahoot-color-' + (idx % 4), getKahootOptionClasses(opt, el.id)]"
                  :disabled="answeredQuestions[el.id] !== undefined"
                  @click="selectOption(el, opt)"
                >
                  <div class="d-flex align-center gap-3">
                    <span class="kahoot-shape-icon">{{ ['▲', '◆', '●', '■'][idx % 4] }}</span>
                    <span class="text-subtitle-1 font-weight-bold text-white">{{ opt.text }}</span>
                  </div>
                  <v-icon
                    v-if="answeredQuestions[el.id] !== undefined && opt.isCorrect"
                    icon="mdi-check-circle"
                    color="white"
                    size="28"
                  ></v-icon>
                  <v-icon
                    v-else-if="selectedAnswers[el.id] === opt.id && !opt.isCorrect"
                    icon="mdi-close-circle"
                    color="white"
                    size="28"
                  ></v-icon>
                </button>
              </div>

              <!-- Immediate Feedback -->
              <div v-if="answeredQuestions[el.id]" class="feedback-card pa-4 rounded-xl mt-3 animate-fade-in" :class="answeredQuestions[el.id].is_correct ? 'bg-green-lighten-5 text-success' : 'bg-amber-lighten-5 text-warning'">
                <div class="d-flex align-center font-weight-bold mb-1">
                  <v-icon :icon="answeredQuestions[el.id].is_correct ? 'mdi-check-circle' : 'mdi-alert-circle'" class="mr-2"></v-icon>
                  <span>{{ answeredQuestions[el.id].is_correct ? 'ตอบได้รวดเร็วและถูกต้อง! (+ ' + (el.points || 30) + ' แต้ม)' : 'คำใบ้เพื่อการเรียนรู้:' }}</span>
                </div>
                <div class="text-caption text-slate-700">
                  {{ answeredQuestions[el.id].explanation || el.explanation || 'เตรียมพร้อมสำหรับข้อต่อไป!' }}
                </div>
                <div class="d-flex justify-end mt-3">
                  <v-btn color="primary" rounded="lg" size="default" class="font-weight-bold px-6" @click="goToNextScene(el.nextScene)">
                    ข้อต่อไป &rarr;
                  </v-btn>
                </div>
              </div>
            </div>

            <!-- Detective & Mystery Quest Mode -->
            <div v-else-if="el.type === 'question' && isDetectiveMode" class="detective-dossier w-100 max-w-2xl animate-fade-in">
              <div class="dossier-folder-tab d-flex justify-space-between align-center px-5 py-3 rounded-t-2xl">
                <div class="d-flex align-center gap-2">
                  <v-icon icon="mdi-incognito" color="amber-darken-4" size="22"></v-icon>
                  <span class="font-weight-black text-subtitle-2 text-slate-900">CONFIDENTIAL CASE FILE #{{ currentSceneIndex + 1 }}</span>
                </div>
                <span class="classified-stamp">TOP SECRET</span>
              </div>

              <div class="dossier-body pa-6 rounded-b-2xl elevation-3 mb-4">
                <div class="d-flex align-center gap-2 text-caption text-brown-darken-2 font-weight-bold mb-2">
                  <v-icon icon="mdi-magnify" size="18"></v-icon>
                  <span>แฟ้มสืบสวนและวิเคราะห์เบาะแส</span>
                </div>
                <h2 class="text-h6 font-weight-bold text-slate-900 mb-4 line-height-relaxed">
                  {{ el.question }}
                </h2>
                <div v-if="el.image" class="mb-4 text-center">
                  <img :src="el.image" class="rounded-lg border elevation-2" style="max-height: 180px; max-width: 100%; object-fit: cover;" />
                </div>

                <div class="evidence-grid d-flex flex-column gap-3 mb-4">
                  <button
                    v-for="(opt, idx) in el.options"
                    :key="opt.id"
                    class="evidence-card pa-4 rounded-xl text-left border transition-all d-flex justify-space-between align-center"
                    :class="getOptionClasses(opt)"
                    :disabled="answeredQuestions[el.id] !== undefined"
                    @click="selectOption(el, opt)"
                  >
                    <div class="d-flex align-center gap-3">
                      <span class="evidence-tag">เบาะแส #{{ idx + 1 }}</span>
                      <span class="text-body-2 font-weight-medium text-slate-900">{{ opt.text }}</span>
                    </div>
                    <v-icon
                      v-if="answeredQuestions[el.id] !== undefined && opt.isCorrect"
                      icon="mdi-check-decagram"
                      color="success"
                      size="22"
                    ></v-icon>
                    <v-icon
                      v-else-if="selectedAnswers[el.id] === opt.id && !opt.isCorrect"
                      icon="mdi-close-octagon"
                      color="error"
                      size="22"
                    ></v-icon>
                  </button>
                </div>

                <div v-if="answeredQuestions[el.id]" class="feedback-card pa-4 rounded-xl mt-3 animate-fade-in" :class="answeredQuestions[el.id].is_correct ? 'bg-green-lighten-5 text-success' : 'bg-amber-lighten-5 text-warning'">
                  <div class="d-flex align-center font-weight-bold mb-1">
                    <v-icon :icon="answeredQuestions[el.id].is_correct ? 'mdi-check-circle' : 'mdi-alert-circle'" class="mr-2"></v-icon>
                    <span>{{ answeredQuestions[el.id].is_correct ? 'ไขปริศนาสำเร็จ! เบาะแสถูกต้อง (+ ' + (el.points || 30) + ' แต้ม)' : 'เบาะแสยังไม่สอดคล้องกับพยานหลักฐาน:' }}</span>
                  </div>
                  <div class="text-caption text-slate-700">
                    {{ answeredQuestions[el.id].explanation || el.explanation || 'พิจารณาข้อเท็จจริงอีกครั้ง!' }}
                  </div>
                  <div class="d-flex justify-end mt-3">
                    <v-btn color="brown-darken-3" rounded="lg" size="small" class="font-weight-bold text-white px-5" @click="goToNextScene(el.nextScene)">
                      สืบสวนด่านถัดไป &rarr;
                    </v-btn>
                  </div>
                </div>
              </div>
            </div>

            <!-- Interactive Visual Novel Mode -->
            <div v-else-if="el.type === 'question' && isVisualNovelMode" class="vn-theater w-100 max-w-2xl animate-fade-in">
              <div class="vn-dialogue-panel pa-6 rounded-2xl mb-4 elevation-4">
                <div class="d-flex align-center gap-3 mb-3">
                  <v-avatar size="48" class="elevation-2 bg-indigo-lighten-4">
                    <v-icon icon="mdi-account-voice" color="indigo" size="28"></v-icon>
                  </v-avatar>
                  <div>
                    <div class="vn-speaker-tag px-3 py-1 rounded-pill">บทสนทนาสถานการณ์</div>
                    <div class="text-caption text-grey-lighten-1">ทางแยกการตัดสินใจ (Story Branch)</div>
                  </div>
                </div>

                <div class="vn-narrative-text text-body-1 font-weight-medium text-white mb-4 line-height-relaxed">
                  {{ el.question }}
                </div>

                <div v-if="el.image" class="mb-4 text-center">
                  <img :src="el.image" class="rounded-xl border" style="max-height: 180px; max-width: 100%; object-fit: cover;" />
                </div>

                <div class="vn-choices-container d-flex flex-column gap-3 mb-4">
                  <button
                    v-for="(opt, idx) in el.options"
                    :key="opt.id"
                    class="vn-choice-btn pa-4 rounded-xl text-left transition-all d-flex justify-space-between align-center"
                    :class="getOptionClasses(opt)"
                    :disabled="answeredQuestions[el.id] !== undefined"
                    @click="selectOption(el, opt)"
                  >
                    <span class="text-body-2 font-weight-medium text-white">📖 ทางเลือกที่ {{ idx + 1 }}: {{ opt.text }}</span>
                    <v-icon
                      v-if="answeredQuestions[el.id] !== undefined && opt.isCorrect"
                      icon="mdi-check-circle"
                      color="success"
                    ></v-icon>
                    <v-icon
                      v-else-if="selectedAnswers[el.id] === opt.id && !opt.isCorrect"
                      icon="mdi-close-circle"
                      color="error"
                    ></v-icon>
                  </button>
                </div>

                <div v-if="answeredQuestions[el.id]" class="feedback-card pa-4 rounded-xl mt-3 animate-fade-in" :class="answeredQuestions[el.id].is_correct ? 'bg-green-lighten-5 text-success' : 'bg-amber-lighten-5 text-warning'">
                  <div class="d-flex align-center font-weight-bold mb-1">
                    <v-icon :icon="answeredQuestions[el.id].is_correct ? 'mdi-check-circle' : 'mdi-alert-circle'" class="mr-2"></v-icon>
                    <span>{{ answeredQuestions[el.id].is_correct ? 'บทสนทนาดำเนินไปอย่างยอดเยี่ยม! (+ ' + (el.points || 25) + ' แต้ม)' : 'ผลลัพธ์ของทางเลือกนี้:' }}</span>
                  </div>
                  <div class="text-caption text-slate-700">
                    {{ answeredQuestions[el.id].explanation || el.explanation || 'ดำเนินเรื่องราวต่อสู่ฉากถัดไป' }}
                  </div>
                  <div class="d-flex justify-end mt-3">
                    <v-btn color="indigo" rounded="lg" size="small" class="font-weight-bold text-white px-5" @click="goToNextScene(el.nextScene)">
                      ดำเนินเนื้อเรื่องต่อ &rarr;
                    </v-btn>
                  </div>
                </div>
              </div>
            </div>

            <!-- Standard Quiz Question Element (Non-RPG / Fallback) -->
            <div v-else-if="el.type === 'question'" class="quiz-box w-100 pa-6 rounded-2xl bg-white elevation-3 border-card">
              <div class="d-flex justify-space-between align-center mb-3">
                <v-chip size="small" color="primary" variant="flat" class="font-weight-medium">
                  ภารกิจคำถาม (+{{ el.points || 10 }} คะแนน)
                </v-chip>
                <span class="text-caption text-grey">เลือกคำตอบที่ถูกต้อง</span>
              </div>

              <h2 class="text-h6 font-weight-bold text-slate-800 mb-4">
                {{ el.question }}
              </h2>

              <div v-if="el.image" class="mb-4 text-center">
                <img :src="el.image" class="rounded-xl elevation-1" style="max-height: 180px; max-width: 100%; object-fit: cover;" />
              </div>

              <!-- Options List -->
              <div class="options-container d-flex flex-column gap-3 mb-4">
                <button
                  v-for="opt in el.options"
                  :key="opt.id"
                  class="option-card pa-4 rounded-xl text-left transition-all border d-flex justify-space-between align-center"
                  :class="getOptionClasses(opt)"
                  :disabled="answeredQuestions[el.id] !== undefined"
                  @click="selectOption(el, opt)"
                >
                  <span class="text-body-2 font-weight-medium">{{ opt.text }}</span>
                  <v-icon
                    v-if="answeredQuestions[el.id] !== undefined && opt.isCorrect"
                    icon="mdi-check-circle"
                    color="success"
                  ></v-icon>
                  <v-icon
                    v-else-if="selectedAnswers[el.id] === opt.id && !opt.isCorrect"
                    icon="mdi-close-circle"
                    color="error"
                  ></v-icon>
                </button>
              </div>

              <!-- Immediate Educational Feedback per spec Section 31 -->
              <div v-if="answeredQuestions[el.id]" class="feedback-card pa-4 rounded-xl mt-3 animate-fade-in" :class="answeredQuestions[el.id].is_correct ? 'bg-green-lighten-5 text-success' : 'bg-amber-lighten-5 text-warning'">
                <div class="d-flex align-center font-weight-bold mb-1">
                  <v-icon :icon="answeredQuestions[el.id].is_correct ? 'mdi-check-circle' : 'mdi-alert-circle'" class="mr-2"></v-icon>
                  <span>{{ answeredQuestions[el.id].is_correct ? 'ถูกต้องยอดเยี่ยม! (+ ' + (el.points || 10) + ' แต้ม)' : 'คำใบ้เพื่อการเรียนรู้:' }}</span>
                </div>
                <div class="text-caption text-slate-700">
                  {{ answeredQuestions[el.id].explanation || el.explanation || 'ขอให้เรียนรู้จากคำตอบและก้าวต่อไป!' }}
                </div>

                <div class="d-flex justify-end mt-3">
                  <v-btn
                    color="primary"
                    rounded="lg"
                    size="small"
                    class="font-weight-bold"
                    @click="goToNextScene(el.nextScene)"
                  >
                    ด่านต่อไป &rarr;
                  </v-btn>
                </div>
              </div>
            </div>

            <!-- 3. Direct Completion Trigger -->
            <div v-else-if="el.type === 'completion'" class="w-100 text-center">
              <v-btn
                class="ai-gradient-bg text-white font-weight-bold px-8 py-3"
                size="x-large"
                rounded="xl"
                prepend-icon="mdi-trophy"
                @click="finishGame"
              >
                ดูสรุปผลคะแนนภารกิจ
              </v-btn>
            </div>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAlertStore } from '@/stores/alert'
import apiClient from '@/api/client'
import confetti from 'canvas-confetti'
import type { GameSchema, GameScene, GameElement, GameOption } from '@/types'

const route = useRoute()
const router = useRouter()
const alertStore = useAlertStore()

const loading = ref(true)
const error = ref('')
const schema = ref<GameSchema | null>(null)
const sessionId = ref<number | null>(null)

// Game State
const currentSceneIndex = ref(0)
const currentScore = ref(0)
const timeRemaining = ref(300)
const durationSpent = ref(0)
const isGameComplete = ref(false)
const correctAnswersCount = ref(0)

// 2D RPG Battle State
const heroHp = ref(100)
const bossHp = ref(100)
const isHeroAttacking = ref(false)
const isBossHit = ref(false)
const isHeroHit = ref(false)
const combatText = ref('')
const combatEffectType = ref<'hero_crit' | 'boss_crit' | 'normal' | ''>('')

const currentGameGenre = computed<'rpg' | 'kahoot' | 'detective' | 'visual_novel' | 'quiz'>(() => {
  if (!schema.value) return 'quiz'
  const g = (schema.value.genre || '').toLowerCase()
  if (g === 'rpg_quest' || g.includes('rpg') || schema.value.settings?.turnBasedCombat === true) return 'rpg'
  if (g === 'live_quiz' || g.includes('live') || g.includes('kahoot') || schema.value.mode === 'multiplayer_live' || schema.value.settings?.isLiveRoom === true || g.includes('team') || g.includes('royale') || g.includes('board')) return 'kahoot'
  if (g === 'scenario_detective' || g.includes('detective') || g.includes('mystery')) return 'detective'
  if (g === 'visual_novel' || g.includes('novel')) return 'visual_novel'
  return 'quiz'
})

const isHtml5Game = computed(() => {
  return schema.value?.type === 'html5' || !!schema.value?.bundle || !!(schema.value as any)?.html
})

function handleHtml5Message(e: MessageEvent) {
  if (e.data?.type === 'dtg:score_update') {
    currentScore.value = e.data.score || 0
  } else if (e.data?.type === 'dtg:game_over') {
    currentScore.value = e.data.score || currentScore.value
    if (e.data.won) {
      confetti({ particleCount: 120, spread: 80, origin: { y: 0.6 } })
    }
  }
}

const isRpgMode = computed(() => currentGameGenre.value === 'rpg')
const isKahootMode = computed(() => currentGameGenre.value === 'kahoot')
const isDetectiveMode = computed(() => currentGameGenre.value === 'detective')
const isVisualNovelMode = computed(() => currentGameGenre.value === 'visual_novel')

function getKahootOptionClasses(opt: GameOption, questionId: string) {
  if (answeredQuestions.value[questionId] === undefined) {
    return 'kahoot-block-idle'
  }
  if (opt.isCorrect) {
    return 'kahoot-block-correct'
  }
  if (selectedAnswers.value[questionId] === opt.id) {
    return 'kahoot-block-wrong'
  }
  return 'kahoot-block-faded'
}

function getBossName(sceneIdx: number) {
  const bosses = [
    'โกเลมเงามืดแห่งความไม่รู้ (Shadow Golem)',
    'อสูรพิทักษ์มิติตรรกะ (Logic Beast)',
    'ราชามังกรแห่งบททดสอบ (Archdragon Boss)',
    'จอมมารบั๊ก & ลูปอนันต์ (Bug Overlord)'
  ]
  return bosses[sceneIdx % bosses.length]
}

function getBossAvatar(sceneIdx: number) {
  const seeds = ['ShadowGolem', 'LogicBeast', 'ArchDragon', 'BugOverlord']
  const seed = seeds[sceneIdx % seeds.length]
  return `https://api.dicebear.com/7.x/bottts/svg?seed=${seed}&colors=red,purple,amber`
}

function getRpgOptionClasses(opt: GameOption, questionId: string) {
  if (answeredQuestions.value[questionId] === undefined) {
    return 'spell-btn-idle'
  }
  if (opt.isCorrect) {
    return 'spell-btn-correct'
  }
  if (selectedAnswers.value[questionId] === opt.id) {
    return 'spell-btn-wrong'
  }
  return 'spell-btn-disabled'
}

async function handleRpgSelectOption(element: GameElement, option: GameOption) {
  if (answeredQuestions.value[element.id]) return

  const isCorrect = option.isCorrect
  if (isCorrect) {
    // Hero attacks Boss
    isHeroAttacking.value = true
    setTimeout(() => {
      isHeroAttacking.value = false
      isBossHit.value = true
      combatText.value = '💥 CRITICAL HIT! -35 HP'
      combatEffectType.value = 'hero_crit'
      bossHp.value = Math.max(0, bossHp.value - 35)

      setTimeout(() => {
        isBossHit.value = false
      }, 600)
      setTimeout(() => {
        combatText.value = ''
      }, 1800)
    }, 350)
  } else {
    // Boss counters Hero
    setTimeout(() => {
      isHeroHit.value = true
      combatText.value = '💔 โดนสวนกลับ! -25 DMG'
      combatEffectType.value = 'boss_crit'
      heroHp.value = Math.max(10, heroHp.value - 25)

      setTimeout(() => {
        isHeroHit.value = false
      }, 600)
      setTimeout(() => {
        combatText.value = ''
      }, 1800)
    }, 200)
  }

  // Record answer via common function
  await selectOption(element, option)
}

function goToNextRpgStage(nextSceneId?: string) {
  bossHp.value = 100
  combatText.value = ''
  goToNextScene(nextSceneId)
}

const selectedAnswers = ref<Record<string, string>>({})
const answeredQuestions = ref<Record<string, any>>({})

let timerInterval: any = null

const currentScene = computed<GameScene | null>(() => {
  if (!schema.value || !schema.value.scenes) return null
  return schema.value.scenes[currentSceneIndex.value] || null
})

const progressPercent = computed(() => {
  if (!schema.value?.scenes || schema.value.scenes.length === 0) return 0
  return Math.round(((currentSceneIndex.value + 1) / schema.value.scenes.length) * 100)
})

function formatTime(seconds: number) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
}

function getOptionClasses(opt: GameOption) {
  const qId = currentScene.value?.elements[0]?.id
  if (!qId || answeredQuestions.value[qId] === undefined) {
    return 'bg-white hover-opt'
  }
  if (opt.isCorrect) {
    return 'bg-green-lighten-5 border-success text-success font-weight-bold'
  }
  if (selectedAnswers.value[qId] === opt.id) {
    return 'bg-red-lighten-5 border-error text-error'
  }
  return 'bg-grey-lighten-4 opacity-50'
}

async function selectOption(element: GameElement, option: GameOption) {
  if (answeredQuestions.value[element.id]) return

  selectedAnswers.value[element.id] = option.id

  try {
    if (sessionId.value) {
      const { data } = await apiClient.post(`/game-sessions/${sessionId.value}/answer`, {
        question_id: element.id,
        selected_answer: option.id,
        time_spent_seconds: 5,
      })

      answeredQuestions.value[element.id] = data
      currentScore.value = data.total_score
      if (data.is_correct) {
        correctAnswersCount.value++
      }
    } else {
      // Local preview mode
      const isCorrect = option.isCorrect
      const pts = isCorrect ? (element.points || 10) : 0
      currentScore.value += pts
      if (isCorrect) correctAnswersCount.value++
      answeredQuestions.value[element.id] = {
        is_correct: isCorrect,
        explanation: element.explanation,
      }
    }
  } catch (err) {
    console.error('Answer submission error', err)
  }
}

function goToNextScene(nextSceneId?: string) {
  if (nextSceneId) {
    const targetIdx = schema.value?.scenes?.findIndex((s) => s.id === nextSceneId)
    if (targetIdx !== undefined && targetIdx >= 0) {
      currentSceneIndex.value = targetIdx
      return
    }
  }

  if (currentSceneIndex.value < (schema.value?.scenes?.length || 1) - 1) {
    currentSceneIndex.value++
  } else {
    finishGame()
  }
}

async function finishGame() {
  isGameComplete.value = true
  if (timerInterval) clearInterval(timerInterval)

  // Fire celebratory confetti!
  confetti({
    particleCount: 100,
    spread: 70,
    origin: { y: 0.6 },
  })

  if (sessionId.value) {
    try {
      await apiClient.post(`/game-sessions/${sessionId.value}/complete`, {
        duration_seconds: durationSpent.value,
      })
    } catch (err) {
      console.error('Failed to complete session', err)
    }
  }
}

function calculateStars() {
  if (currentScore.value >= 80) return 3
  if (currentScore.value >= 50) return 2
  return 1
}

function restartGame() {
  currentSceneIndex.value = 0
  currentScore.value = 0
  durationSpent.value = 0
  correctAnswersCount.value = 0
  heroHp.value = 100
  bossHp.value = 100
  combatText.value = ''
  answeredQuestions.value = {}
  selectedAnswers.value = {}
  isGameComplete.value = false
  timeRemaining.value = schema.value?.settings?.duration || 300
  startTimer()
}

function startTimer() {
  if (timerInterval) clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    durationSpent.value++
    if (timeRemaining.value > 0) {
      timeRemaining.value--
    } else {
      finishGame()
    }
  }, 1000)
}

async function confirmExit() {
  const confirmed = await alertStore.confirm(
    'คุณต้องการออกจากเกมใช่หรือไม่? ผลการเล่นจะถูกบันทึก',
    'ออกจากเกม',
    { confirmText: 'ออกจากเกม', cancelText: 'เล่นต่อ', type: 'warning' }
  )
  if (confirmed) {
    router.push('/dashboard')
  }
}

onMounted(async () => {
  window.addEventListener('message', handleHtml5Message)
  const publicId = route.params.publicId as string
  const isPreview = route.query.preview !== undefined ? String(route.query.preview) : 'true'
  try {
    const { data } = await apiClient.get(`/public/games/${publicId}`, {
      params: { preview: isPreview }
    })
    schema.value = data.schema
    timeRemaining.value = data.schema?.settings?.duration || 300

    // Try starting a session if authenticated
    try {
      const sessionRes = await apiClient.post('/game-sessions/start', {
        game_id: data.id,
      })
      sessionId.value = sessionRes.data.session.id
    } catch {
      // Unauthenticated preview mode
    }

    startTimer()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'ไม่สามารถโหลดข้อมูลเกมได้'
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  window.removeEventListener('message', handleHtml5Message)
  if (timerInterval) clearInterval(timerInterval)
})
</script>

<style scoped>
.player-container {
  width: 100vw;
  height: 100vh;
  background: radial-gradient(circle at 50% 20%, #1e1b4b 0%, #0f172a 100%);
  overflow-y: auto;
}
.player-header {
  background-color: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.player-canvas {
  min-height: calc(100vh - 70px);
}
.line-height-relaxed {
  line-height: 1.7;
}
.option-card {
  width: 100%;
  cursor: pointer;
  border: 1.5px solid #e2e8f0;
}
.hover-opt:hover {
  border-color: #c670ff;
  background-color: #faf4fe;
}
.gap-3 { gap: 12px; }
.max-w-xl { max-width: 620px; }
.max-w-lg { max-width: 500px; }

/* 2D RPG Battle Arena Styling */
.max-w-2xl {
  max-width: 720px;
}
.rpg-stage-header {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(139, 92, 246, 0.3);
  backdrop-filter: blur(8px);
}
.battle-badge {
  background: linear-gradient(135deg, #7c3aed, #ec4899);
  color: white;
  font-size: 11px;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 9999px;
  letter-spacing: 0.5px;
}
.battlefield-card {
  background: linear-gradient(180deg, #090d16 0%, #171f33 100%);
  border: 1.5px solid rgba(139, 92, 246, 0.4);
  box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.8), 0 0 25px rgba(124, 58, 237, 0.2);
}
.battle-grid-overlay {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 24px 24px;
  pointer-events: none;
}
.hero-avatar-border {
  border: 3px solid #38bdf8;
  box-shadow: 0 0 20px rgba(56, 189, 248, 0.5);
}
.boss-avatar-border {
  border: 3px solid #f43f5e;
  box-shadow: 0 0 20px rgba(244, 63, 94, 0.6);
}
.unit-lvl-badge {
  position: absolute;
  bottom: -4px;
  left: 50%;
  transform: translateX(-50%);
  background: #0284c7;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  padding: 1px 8px;
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.4);
}
.boss-lvl-badge {
  position: absolute;
  bottom: -4px;
  left: 50%;
  transform: translateX(-50%);
  background: #e11d48;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  padding: 1px 8px;
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.4);
}
.hp-gauge-container {
  min-width: 140px;
}
.hp-track {
  height: 8px;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 4px;
  overflow: hidden;
}
.hero-hp-fill {
  background: linear-gradient(90deg, #10b981, #34d399);
  transition: width 0.4s ease-out;
}
.boss-hp-fill {
  background: linear-gradient(90deg, #ef4444, #f87171);
  transition: width 0.4s ease-out;
}
.mp-track {
  height: 5px;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 3px;
  overflow: hidden;
}
.mp-fill {
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
}
.vs-emblem {
  font-size: 28px;
  font-weight: 900;
  font-style: italic;
  background: linear-gradient(180deg, #facc15, #ea580c);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 2px 8px rgba(245, 158, 11, 0.6));
}
.turn-indicator {
  background: rgba(124, 58, 237, 0.3);
  border: 1px solid rgba(167, 139, 250, 0.4);
  color: #e9d5ff;
  font-size: 11px;
}

/* Floating Damage Numbers */
.floating-damage-text {
  position: absolute;
  top: 35%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 26px;
  font-weight: 900;
  z-index: 10;
  pointer-events: none;
  text-shadow: 0 3px 12px rgba(0, 0, 0, 0.8);
  white-space: nowrap;
}
.hero_crit {
  color: #fef08a;
  text-shadow: 0 0 20px #f59e0b, 0 0 35px #d97706;
}
.boss_crit {
  color: #fca5a5;
  text-shadow: 0 0 20px #ef4444, 0 0 35px #b91c1c;
}
.damage-pop-enter-active {
  animation: popFloat 1.2s cubic-bezier(0.18, 0.89, 0.32, 1.28) forwards;
}
.damage-pop-leave-active {
  opacity: 0;
  transition: opacity 0.3s;
}
@keyframes popFloat {
  0% { transform: translate(-50%, 0) scale(0.6); opacity: 0; }
  25% { transform: translate(-50%, -25px) scale(1.2); opacity: 1; }
  70% { transform: translate(-50%, -45px) scale(1); opacity: 1; }
  100% { transform: translate(-50%, -65px) scale(0.9); opacity: 0; }
}

/* Combat Battle Animations */
@keyframes heroLunge {
  0% { transform: translateX(0); }
  45% { transform: translateX(55px) scale(1.08); }
  100% { transform: translateX(0); }
}
.hero-lunging {
  animation: heroLunge 0.35s ease-in-out;
}
@keyframes unitShake {
  0%, 100% { transform: translateX(0); filter: drop-shadow(0 0 0 transparent); }
  20%, 60% { transform: translateX(-8px); filter: drop-shadow(0 0 16px #ef4444); }
  40%, 80% { transform: translateX(8px); filter: drop-shadow(0 0 16px #ef4444); }
}
.unit-hit {
  animation: unitShake 0.4s ease-in-out;
}

/* RPG Question Scroll */
.rpg-question-scroll {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(245, 158, 11, 0.35);
  backdrop-filter: blur(10px);
  box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.5);
}

/* Action Command Deck */
.spell-actions-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}
@media (max-width: 600px) {
  .spell-actions-grid {
    grid-template-columns: 1fr;
  }
}
.spell-command-btn {
  background: rgba(30, 41, 59, 0.7);
  border: 1.5px solid rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(10px);
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
.spell-btn-idle:hover {
  background: rgba(99, 102, 241, 0.25);
  border-color: #818cf8;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.35);
}
.spell-btn-correct {
  background: rgba(16, 185, 129, 0.25) !important;
  border-color: #10b981 !important;
  box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
}
.spell-btn-wrong {
  background: rgba(239, 68, 68, 0.25) !important;
  border-color: #ef4444 !important;
}
.spell-btn-disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.spell-icon-badge {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
}
.spell-slot-0 { border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.1); }
.spell-slot-1 { border-color: rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.1); }
.spell-slot-2 { border-color: rgba(59, 130, 246, 0.4); background: rgba(59, 130, 246, 0.1); }
.spell-slot-3 { border-color: rgba(168, 85, 247, 0.4); background: rgba(168, 85, 247, 0.1); }

/* Combat Feedback */
.combat-feedback-box {
  backdrop-filter: blur(12px);
}
.feedback-victory {
  background: rgba(6, 78, 59, 0.85);
  border: 1px solid #10b981;
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.25);
}
.feedback-defeat {
  background: rgba(120, 53, 15, 0.85);
  border: 1px solid #f59e0b;
  box-shadow: 0 10px 25px rgba(245, 158, 11, 0.25);
}

/* Kahoot Arena Styles */
.kahoot-header {
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(245, 158, 11, 0.4);
  backdrop-filter: blur(10px);
}
.kahoot-live-badge {
  background: linear-gradient(135deg, #f59e0b, #ef4444);
  color: white;
  font-size: 11px;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 9999px;
  letter-spacing: 0.5px;
}
.kahoot-timer-pill {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid #ef4444;
  color: #fca5a5;
  font-weight: 800;
  font-size: 12px;
}
.kahoot-question-billboard {
  background: white;
  border: 2px solid #e2e8f0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}
.kahoot-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}
@media (max-width: 600px) {
  .kahoot-grid {
    grid-template-columns: 1fr;
  }
}
.kahoot-block-btn {
  border: none;
  cursor: pointer;
  min-height: 80px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  transition: transform 0.15s, box-shadow 0.15s;
}
.kahoot-block-idle:hover {
  transform: translateY(-3px) scale(1.02);
  filter: brightness(1.1);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.35);
}
.kahoot-color-0 { background: #e21b3c !important; }
.kahoot-color-1 { background: #1368ce !important; }
.kahoot-color-2 { background: #d89e00 !important; }
.kahoot-color-3 { background: #26890c !important; }
.kahoot-shape-icon {
  font-size: 24px;
  color: rgba(255, 255, 255, 0.9);
}
.kahoot-block-faded {
  opacity: 0.35;
  cursor: not-allowed;
}
.kahoot-block-correct {
  box-shadow: 0 0 25px #10b981 !important;
  border: 3px solid #fff !important;
}
.kahoot-block-wrong {
  opacity: 0.5;
}

/* Detective Dossier Styles */
.dossier-folder-tab {
  background: #d97706;
  border-bottom: 2px solid #b45309;
}
.classified-stamp {
  background: #dc2626;
  color: white;
  font-size: 10px;
  font-weight: 900;
  letter-spacing: 1.5px;
  padding: 2px 8px;
  border-radius: 4px;
  transform: rotate(3deg);
}
.dossier-body {
  background: #fffbeb;
  border: 2px solid #fde68a;
  border-top: none;
}
.evidence-card {
  background: white;
  cursor: pointer;
  border: 1.5px solid #e5e7eb;
}
.evidence-card:hover {
  background: #fef3c7;
  border-color: #d97706;
}
.evidence-tag {
  background: #f3f4f6;
  color: #4b5563;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}

/* Visual Novel Styles */
.vn-dialogue-panel {
  background: linear-gradient(180deg, rgba(30, 27, 75, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%);
  border: 1.5px solid rgba(129, 140, 248, 0.4);
  backdrop-filter: blur(16px);
}
.vn-speaker-tag {
  background: linear-gradient(135deg, #6366f1, #a855f7);
  color: white;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}
.vn-choice-btn {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  cursor: pointer;
}
.vn-choice-btn:hover {
  background: rgba(99, 102, 241, 0.3);
  border-color: #818cf8;
}
</style>
