export type UserRole = 'admin' | 'teacher' | 'student'

export interface User {
  id: number
  name: string
  email: string
  username?: string
  role: UserRole
  student_id?: string
  avatar?: string
}

export interface Classroom {
  id: number
  teacher_id: number
  name: string
  code: string
  description?: string
  academic_year?: string
  semester?: string
  cover_image?: string
  theme_color?: string
  status: 'active' | 'archived'
  students?: User[]
  assignments?: GameAssignment[]
  created_at?: string
}

export interface DesignProject {
  id: number
  teacher_id: number
  title: string
  description?: string
  subject?: string
  grade_level?: string
  game_mode?: 'single' | 'multiplayer_live'
  game_genre?: string
  theme_pack?: string
  current_step: number
  status: 'draft' | 'in_progress' | 'ready' | 'generated' | 'published' | 'archived'
  empathize?: DesignEmpathize
  define?: DesignDefine
  ideate?: DesignIdeate
  prototype?: DesignPrototype
  test?: DesignTest
  games?: Game[]
  updated_at?: string
}

export interface DesignEmpathize {
  id?: number
  project_id?: number
  target_learner?: string
  age_group?: string
  grade_level?: string
  subject?: string
  learning_context?: string
  learner_characteristics?: string
  existing_knowledge?: string
  interests?: string
  learning_difficulties?: string
  pain_points?: string
  learning_environment?: string
  device_availability?: string
  ai_notes?: any
}

export interface DesignDefine {
  id?: number
  project_id?: number
  problem_statement?: string
  learning_problem?: string
  learning_objectives?: string[]
  expected_outcomes?: string
  knowledge_goals?: string
  skill_goals?: string
  attitude_goals?: string
  success_criteria?: string
  ai_notes?: any
}

export interface DesignIdeate {
  id?: number
  project_id?: number
  game_concept?: string
  game_genre?: string
  theme?: string
  story?: string
  game_mechanics?: string[]
  challenges?: string
  missions?: string
  rewards?: string
  interaction_type?: string
  difficulty?: string
  duration_minutes?: number
  ai_ideas?: any
}

export interface DesignPrototype {
  id?: number
  project_id?: number
  scene_outline?: any[]
  character_roles?: any[]
  core_rules?: string[]
  feedback_mechanisms?: string
  ai_notes?: any
}

export interface DesignTest {
  id?: number
  project_id?: number
  test_date?: string
  test_users_count?: number
  observations?: string
  recorded_bugs?: string[]
  difficulty_rating?: number
  feedback_summary?: string
  ai_recommendations?: any
}

export interface Game {
  id: number
  teacher_id: number
  project_id?: number
  public_id: string
  title: string
  description?: string
  theme?: string
  genre?: string
  cover_image?: string
  status: 'draft' | 'testing' | 'published' | 'unpublished' | 'archived'
  current_version_id?: number
  current_version?: GameVersion
  versions?: GameVersion[]
  assignments?: GameAssignment[]
  teacher?: { id: number; name: string }
  created_at?: string
  updated_at?: string
}

export interface GameVersion {
  id: number
  game_id: number
  version_number: string
  schema_data: string | GameSchema
  changelog?: string
  is_published: boolean
  created_at?: string
}

export interface GameAssignment {
  id: number
  classroom_id: number
  game_id: number
  game_version_id?: number
  start_at?: string
  due_at?: string
  max_attempts: number
  passing_score: number
  show_score: boolean
  allow_replay: boolean
  status: 'active' | 'closed'
  classroom?: Classroom
  game?: Game
}

// Game Schema v1.0 Standard Definition
export interface GameSchema {
  version: string
  title: string
  description?: string
  theme: string
  mode?: string
  genre?: string
  settings: {
    duration: number
    maxAttempts: number
    allowSound?: boolean
    passingScore?: number
    turnBasedCombat?: boolean
    [key: string]: any
  }
  scenes: GameScene[]
  scoring: {
    initialScore: number
    maxScore: number
    passingScore: number
  }
  completion: {
    type: string
    rewardTitle?: string
  }
}

export interface GameScene {
  id: string
  title: string
  background?: string
  elements: GameElement[]
}

export type GameElementType = 'character' | 'question' | 'dialogue' | 'object' | 'text' | 'image' | 'completion'

export interface GameElement {
  id: string
  type: GameElementType
  name?: string
  avatar?: string
  x?: number
  y?: number
  width?: number
  height?: number
  dialogue?: {
    speaker?: string
    text: string
    actionText?: string
    nextScene?: string
  }
  questionType?: 'multiple_choice' | 'true_false' | 'drag_drop' | 'matching'
  question?: string
  image?: string
  options?: GameOption[]
  points?: number
  explanation?: string
  nextScene?: string
  title?: string
  message?: string
}

export interface GameOption {
  id: string
  text: string
  isCorrect: boolean
}

export interface GameSession {
  id: number
  student_id: number
  game_id: number
  session_token: string
  started_at: string
  completed_at?: string
  duration_seconds: number
  score: number
  max_score: number
  progress_percent: number
  attempt_number: number
  status: 'in_progress' | 'completed' | 'abandoned'
}

export interface GameScore {
  id: number
  session_id: number
  student_id: number
  total_score: number
  max_possible_score: number
  percentage: number
  passed: boolean
}

export interface Asset {
  id: number
  name: string
  description?: string
  type: 'character' | 'background' | 'object' | 'item' | 'ui' | 'icon' | 'audio'
  file_path: string
  preview_url?: string
  theme?: string
  style?: string
  is_public: boolean
}

export interface AssetPack {
  id: number
  name: string
  slug: string
  description?: string
  theme?: string
  cover_image?: string
  assets_count?: number
}
