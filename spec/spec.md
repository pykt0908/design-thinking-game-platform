# Design Thinking Game Studio — Product Specification

## 1. Project Overview

**Project Name:** Design Thinking Game Studio  
**Project Type:** Web Application / Educational Game Authoring Platform  
**Primary Language:** Thai  
**Target Users:** Admin, Teacher, Student

Design Thinking Game Studio คือแพลตฟอร์มสำหรับช่วยครูออกแบบและสร้าง Web-based Learning Game ผ่านกระบวนการ Design Thinking โดยครูไม่จำเป็นต้องมีความรู้ด้าน Programming

กระบวนการหลักของระบบคือ

Empathize → Define → Ideate → Prototype → Test → Generate Game → Publish → Student Play → Analytics → Improve

ระบบจะใช้ AI ช่วยวิเคราะห์ข้อมูลจากแต่ละขั้นตอนของ Design Thinking และแปลงข้อมูลเหล่านั้นเป็น Game Specification ก่อนสร้างเป็นเกมผ่าน Game Engine ของระบบ

AI ต้องไม่สร้าง HTML/JavaScript แบบอิสระโดยตรง แต่ต้องสร้างข้อมูลในรูปแบบ Game Schema ที่ระบบกำหนด เพื่อให้สามารถควบคุม Gameplay, Scoring, Analytics และความปลอดภัยได้

---

# 2. Technology Stack

## Frontend

- Vue 3
- TypeScript
- Vite
- Vuetify 3
- Pinia
- Vue Router
- Axios
- VueUse

## Backend

- Laravel 12
- PHP 8.3+
- Laravel Sanctum
- Laravel Queue
- Laravel Scheduler
- Laravel Storage
- Laravel Events / Listeners
- Laravel Notifications

## Database

- MySQL 8+

## Optional Infrastructure

- Redis
- S3-compatible Object Storage
- CDN
- Vector Database / Vector Search

Redis และ Vector Search ยังไม่จำเป็นสำหรับ MVP

---

# 3. System Architecture

```text
Vue 3 + Vuetify
        |
        | REST API
        v
Laravel 12
        |
        +-- Authentication
        +-- Design Thinking
        +-- Game Management
        +-- Game Schema
        +-- AI Service
        +-- Asset Library
        +-- Classroom
        +-- Gameplay
        +-- Scoring
        +-- Analytics
        |
        v
MySQL

        +
        |
        +---- Object Storage
        |
        +---- AI Providers
```

---

# 4. User Roles

## 4.1 Admin

สามารถ

- จัดการผู้ใช้งาน
- จัดการครู
- จัดการนักเรียน
- จัดการ Asset Library
- จัดการ Asset Pack
- Import Asset
- จัดการ Game Template
- จัดการ AI Provider
- ดู AI Usage
- ดู Game Usage
- ดู Storage Usage
- ตั้งค่าระบบ
- จัดการหมวดหมู่
- จัดการ Tags
- ตรวจสอบ Published Games

---

## 4.2 Teacher

สามารถ

- สร้าง Classroom
- เพิ่มนักเรียน
- สร้าง Design Thinking Project
- ทำ Design Thinking Workflow
- ใช้ AI Assistant
- Generate Game
- แก้ไข Game
- เลือก Asset
- Upload Asset
- Generate AI Asset
- Preview Game
- Publish Game
- Assign Game ให้ Classroom
- ดูคะแนนนักเรียน
- ดู Gameplay Analytics
- ดู Student Progress
- ปรับปรุง Game
- Duplicate Game
- สร้าง Game Version
- ตั้งค่า AI Provider/API Key ของตัวเอง

---

## 4.3 Student

สามารถ

- Login
- Join Classroom
- ดูเกมที่ได้รับมอบหมาย
- เล่นเกม
- Resume Game
- ส่งคำตอบ
- ได้รับคะแนน
- ดูผลการเล่น
- ดู Achievement
- เล่นใหม่หากครูอนุญาต

Student ไม่สามารถเข้าถึง Game Builder, Teacher AI Key หรือ Game Configuration ภายในได้

---

# 5. Authentication

ใช้ Laravel Sanctum

รองรับ

- Email / Password
- Username / Password
- Student ID / Password

โครงสร้าง Role:

```text
admin
teacher
student
```

Route ต้องถูกป้องกันด้วย Role Middleware

ตัวอย่าง:

```text
/admin/*
/teacher/*
/student/*
```

---

# 6. Teacher Dashboard

Dashboard ต้องแสดง

- จำนวน Classroom
- จำนวน Student
- จำนวน Game
- Published Games
- Draft Games
- จำนวนครั้งที่เล่นเกม
- Average Score
- Recent Activities
- Recent Games
- Recent Classes

Quick Actions:

- Create Project
- Create Game
- Create Classroom
- Import Asset

---

# 7. Classroom Management

Teacher สามารถสร้าง Classroom

ข้อมูล Classroom:

- name
- code
- description
- academic_year
- semester
- status

ระบบสร้าง Join Code อัตโนมัติ

ตัวอย่าง:

```text
DTG-X7P2K
```

นักเรียนสามารถใช้ Code เพื่อ Join Classroom

Teacher สามารถ

- Add Student
- Remove Student
- Import Student
- View Student
- View Student Performance

---

# 8. Design Thinking Project

หัวใจหลักของระบบ

Project ประกอบด้วย 5 ขั้นตอน

```text
1. Empathize
2. Define
3. Ideate
4. Prototype
5. Test
```

ทุก Project ต้องมีสถานะ

```text
draft
in_progress
ready
generated
published
archived
```

---

# 9. Empathize

ใช้สำหรับทำความเข้าใจผู้เรียน

ข้อมูลที่ควรมี

- Target Learner
- Age
- Grade Level
- Subject
- Learning Context
- Learner Characteristics
- Existing Knowledge
- Interests
- Learning Difficulties
- Pain Points
- Learning Environment
- Device Availability
- Additional Notes

AI สามารถช่วย

- วิเคราะห์ Learner Persona
- สรุป Pain Points
- แนะนำข้อมูลที่ยังขาด

---

# 10. Define

ใช้กำหนดปัญหาและเป้าหมาย

ข้อมูล

- Problem Statement
- Learning Problem
- Learning Objectives
- Expected Learning Outcomes
- Knowledge
- Skills
- Attitude
- Success Criteria

AI สามารถสร้าง Problem Statement จาก Empathize ได้

---

# 11. Ideate

ใช้คิดแนวทางเกม

ข้อมูล

- Game Concept
- Game Genre
- Theme
- Story
- Game Mechanics
- Challenges
- Missions
- Rewards
- Interaction
- Difficulty
- Game Duration

Game Type ตัวอย่าง

- Quiz Game
- Adventure
- Simulation
- Puzzle
- Matching
- Sorting
- Drag & Drop
- Decision Making
- Scenario Game
- Exploration
- Collect Item
- Hidden Object

AI สามารถ Generate Game Ideas หลายแนวทางให้ครูเลือกได้

---

# 12. Prototype

Prototype จะถูกแปลงเป็น Game Structure

ประกอบด้วย

- Scenes
- Characters
- Objects
- Dialogue
- Questions
- Missions
- Rules
- Scoring
- Feedback
- Navigation

Teacher สามารถแก้ไข Prototype ผ่าน Visual Game Editor

---

# 13. Test

Teacher สามารถ

- Preview Game
- Play Test
- Reset Test
- Invite Test User
- Record Feedback
- Record Bug
- Evaluate Difficulty

ข้อมูล Test ต้องสามารถนำกลับไปให้ AI วิเคราะห์เพื่อปรับเกมได้

---

# 14. AI Game Generator

AI Game Generator เป็น Core Feature

Pipeline:

```text
Design Thinking Project
        ↓
Context Builder
        ↓
AI Game Designer
        ↓
Game Specification
        ↓
Asset Retrieval
        ↓
Game Schema
        ↓
Schema Validation
        ↓
Game Runtime
        ↓
Playable Web Game
```

AI ห้าม Generate executable JavaScript ที่นำไปรันโดยตรง

AI ต้อง Generate ตาม Game Schema เท่านั้น

---

# 15. Game Schema

Game Schema ใช้ JSON

ตัวอย่าง:

```json
{
  "version": "1.0",
  "title": "ภารกิจโรงเรียนไร้ขยะ",
  "description": "เกมเรียนรู้การแยกขยะ",
  "theme": "school",
  "settings": {
    "duration": 600,
    "maxAttempts": 3
  },
  "scenes": [
    {
      "id": "scene_01",
      "type": "scenario",
      "title": "โรงเรียนของเรา",
      "background": null,
      "elements": []
    }
  ],
  "missions": [],
  "questions": [],
  "scoring": {
    "initialScore": 0,
    "maxScore": 100
  },
  "completion": {
    "type": "mission_complete"
  }
}
```

Schema ต้อง Versioned

เช่น

```text
1.0
1.1
2.0
```

---

# 16. Game Components

Game Engine ต้องรองรับ Component แบบ Plugin-like

MVP Components:

- Text
- Image
- Character
- Object
- Button
- Dialogue
- Multiple Choice
- True / False
- Drag & Drop
- Matching
- Sorting
- Collect Item
- Hotspot
- Timer
- Score
- Progress
- Mission
- Feedback
- Scene Transition

Phase ต่อไป:

- Branching Scenario
- Inventory
- NPC
- Map
- Quest
- Puzzle
- Simulation
- Interactive Story

---

# 17. Game Editor

Layout:

```text
+------------------------------------------------+
| Toolbar                                        |
+-------------+----------------------+-----------+
| Components  |                      | Property  |
|             |                      | Inspector |
| Character   |     GAME CANVAS      |           |
| Object      |                      | Position  |
| Dialogue    |                      | Size      |
| Question    |                      | Action    |
| Mission     |                      | Score     |
| Image       |                      | Animation |
+-------------+----------------------+-----------+
| Scenes / Timeline                              |
+------------------------------------------------+
```

ต้องรองรับ

- Drag & Drop
- Select
- Move
- Resize
- Duplicate
- Delete
- Layer Order
- Properties
- Scene Management
- Undo / Redo
- Preview
- Save

---

# 18. Asset Library

Asset Types:

```text
Character
Background
Object
Item
UI
Icon
Audio
Music
Sound Effect
Animation
3D Model
```

Asset Metadata:

- name
- description
- type
- category
- tags
- style
- theme
- file
- preview
- source
- author
- license
- attribution_required
- redistribution_allowed
- commercial_use
- width
- height
- file_type

---

# 19. Asset Sources

รองรับ

## Core Assets

Asset ที่ Platform จัดเตรียม

เช่น Kenney หรือ Asset ที่สร้างเอง

## Teacher Assets

Teacher Upload

เช่น

- School Logo
- Classroom
- Teacher
- Local Place
- Product
- Learning Material

## AI Generated Assets

สร้างผ่าน AI Provider

---

# 20. Asset Importer

Admin สามารถ Upload Asset Pack เช่น ZIP

Pipeline:

```text
Upload ZIP
    ↓
Extract
    ↓
Validate
    ↓
Generate Thumbnail
    ↓
AI Classification
    ↓
Generate Tags
    ↓
License Metadata
    ↓
Store
    ↓
Available in Asset Library
```

---

# 21. Asset Packs

Asset สามารถจัดเป็น Pack

ตัวอย่าง

- School
- Education
- Science
- Agriculture
- Environment
- Space
- Community
- Technology

Game Generator สามารถเลือก Pack ตาม Theme

---

# 22. AI Provider

Teacher สามารถกำหนด AI Provider ของตัวเอง

รองรับ architecture สำหรับ

- OpenAI
- Google Gemini
- Anthropic
- OpenAI-compatible API

ข้อมูล

- Provider
- API Key
- Model
- Base URL (optional)
- Status

มีปุ่ม

```text
Test Connection
```

---

# 23. API Key Security

API Key ห้ามส่งไป Frontend

Flow:

```text
Vue
 ↓
Laravel API
 ↓
AI Service
 ↓
Provider
```

API Key ต้องเข้ารหัสก่อนบันทึก Database

ตัวอย่าง Table:

```text
teacher_ai_credentials

id
teacher_id
provider
encrypted_api_key
model
base_url
is_active
created_at
updated_at
```

API Response ห้าม Return API Key

Frontend แสดงเพียง

```text
sk-••••••••••••1234
```

---

# 24. AI Services

สร้าง Service Layer

```text
AIService
GameDesignService
GameGenerationService
AssetGenerationService
AssetTaggingService
GameImprovementService
```

อย่าเรียก AI Provider โดยตรงจาก Controller

---

# 25. AI Game Improvement

ระบบสามารถใช้ Gameplay Analytics เพื่อช่วยปรับเกม

ตัวอย่าง

```text
Students Play
      ↓
Analytics
      ↓
AI Analysis
      ↓
Suggestion
      ↓
Teacher Approves
      ↓
New Game Version
```

AI อาจแนะนำ

- Question too difficult
- Mission too long
- Instructions unclear
- Difficulty imbalance
- Students fail at specific scene
- Score distribution abnormal

AI ห้ามแก้ Published Game โดยอัตโนมัติ

Teacher ต้อง Approve ก่อนเสมอ

---

# 26. Game Publishing

Game Status

```text
draft
testing
published
unpublished
archived
```

Game มี Public ID

ตัวอย่าง

```text
/game/GME-X8A9K
```

Teacher สามารถ Assign Game ไปยัง Classroom

---

# 27. Game Assignment

ข้อมูล

- classroom
- game
- start_at
- due_at
- max_attempts
- passing_score
- show_score
- allow_replay
- status

---

# 28. Student Game Player

Flow:

```text
Login
 ↓
My Classroom
 ↓
Assignments
 ↓
Game
 ↓
Play
 ↓
Submit
 ↓
Result
```

Game Player ควรเป็น Full Screen และไม่แสดง Teacher Dashboard UI

---

# 29. Gameplay Session

ทุกครั้งที่เล่นสร้าง Session

เก็บ

- student_id
- game_id
- assignment_id
- started_at
- completed_at
- duration
- score
- max_score
- progress
- attempt
- status

---

# 30. Gameplay Events

เก็บ Event ที่สำคัญ

ตัวอย่าง

```text
game_started
scene_entered
mission_started
answer_submitted
item_collected
mission_completed
game_completed
game_exited
```

Event Payload เก็บ JSON

แต่ไม่ควรเก็บ Mouse Movement ทุก Pixel ใน MVP

---

# 31. Scoring

รองรับ

- Correct Answer
- Mission Completion
- Item Collection
- Time Bonus
- Penalty
- Custom Rule

Game Schema เป็นตัวกำหนด Scoring Rule

Server ต้อง Validate คะแนนสำคัญ

ห้ามเชื่อคะแนนที่ส่งมาจาก Browser โดยตรงทั้งหมด

---

# 32. Analytics

Teacher Dashboard แสดง

## Game Analytics

- Total Players
- Completion Rate
- Average Score
- Average Play Time
- Attempts
- Difficult Questions
- Difficult Scenes

## Student Analytics

- Score
- Attempts
- Completion
- Duration
- Answers
- Mission Progress

## Classroom Analytics

- Average Score
- Completion
- Score Distribution
- Student Progress

---

# 33. Game Versioning

ทุก Game รองรับ Version

```text
Game
 |
 +-- Version 1
 +-- Version 2
 +-- Version 3
```

Published Version ต้องไม่ถูกแก้โดยตรง

เมื่อแก้ไขให้สร้าง Draft Version ใหม่

เพื่อป้องกันคะแนนเก่าผูกกับ Game Definition ใหม่

---

# 34. Suggested Database Modules

Main Tables:

```text
users
roles

teacher_profiles
student_profiles

classrooms
classroom_students

design_projects
design_empathize
design_define
design_ideate
design_prototype
design_tests

games
game_versions
game_assignments

game_sessions
game_events
game_answers
game_scores

assets
asset_categories
asset_tags
asset_tag_relations
asset_packs
asset_pack_items
asset_sources

teacher_ai_credentials
ai_requests
ai_generations

notifications
system_settings
```

ใช้ JSON Column เฉพาะข้อมูลที่มี Structure ยืดหยุ่น เช่น Game Schema

ข้อมูลที่ต้อง Query/Report บ่อยควร Normalize เป็น Column/Table

---

# 35. API Structure

ใช้ prefix:

```text
/api/v1
```

ตัวอย่าง

```text
POST   /api/v1/auth/login

GET    /api/v1/projects
POST   /api/v1/projects

GET    /api/v1/projects/{id}
PUT    /api/v1/projects/{id}

POST   /api/v1/projects/{id}/generate-game

GET    /api/v1/games
POST   /api/v1/games

GET    /api/v1/games/{id}
PUT    /api/v1/games/{id}

POST   /api/v1/games/{id}/publish

GET    /api/v1/assets

GET    /api/v1/classrooms
POST   /api/v1/classrooms

GET    /api/v1/assignments

POST   /api/v1/game-sessions
POST   /api/v1/game-sessions/{id}/events
POST   /api/v1/game-sessions/{id}/complete
```

---

# 36. Backend Coding Structure

Laravel ใช้ Service-oriented structure

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Models/
│
├── Services/
│   ├── AI/
│   ├── Game/
│   ├── Asset/
│   ├── Classroom/
│   └── Analytics/
│
├── Actions/
├── Jobs/
├── Events/
├── Listeners/
├── Policies/
└── Enums/
```

Controller ต้องบาง

Business Logic ให้อยู่ใน Service / Action

---

# 37. Frontend Structure

```text
src/
├── api/
├── assets/
├── components/
├── composables/
├── layouts/
├── modules/
│   ├── auth/
│   ├── dashboard/
│   ├── classroom/
│   ├── design-thinking/
│   ├── game-builder/
│   ├── game-player/
│   ├── asset-library/
│   ├── analytics/
│   └── settings/
│
├── router/
├── stores/
├── types/
├── utils/
└── views/
```

ใช้ Feature-based Architecture

หลีกเลี่ยงการรวม Component ทั้งหมดไว้ใน `/components`

---

# 38. State Management

Pinia ใช้สำหรับ

- Authentication
- Current User
- Game Builder State
- Current Project
- Asset Selection
- Editor History
- Notifications

ข้อมูล Server State ไม่ควร Copy เข้า Global Store โดยไม่จำเป็น

---

# 39. Auto Save

Design Thinking Form และ Game Builder ต้อง Auto Save

ใช้ Debounce

ตัวอย่าง

```text
User edits
   ↓
wait 800-1500ms
   ↓
Auto Save
```

แสดง Status

```text
Saving...
Saved
Save failed
```

---

# 40. Queue

งานหนักต้องใช้ Laravel Queue

เช่น

- AI Game Generation
- Asset Generation
- ZIP Import
- Thumbnail Generation
- AI Asset Tagging
- Large Analytics Processing

Frontend ต้อง Poll Status หรือใช้ Event/WebSocket ในอนาคต

---

# 41. Security

ต้องมี

- CSRF Protection
- Authentication
- Authorization
- Policies
- Rate Limiting
- File Validation
- MIME Validation
- API Key Encryption
- Input Validation
- Output Escaping
- Secure Headers

Uploaded Files ห้าม Execute ได้

Game Schema ต้อง Validate ก่อน Render

---

# 42. Responsive Design

Teacher/Admin:

Desktop First แต่รองรับ Tablet

Student Game Player:

ต้องรองรับ

- Desktop
- Tablet
- Mobile

ขั้นต่ำ

```text
360px+
```

---

# 43. MVP Scope

Phase 1 ต้องทำให้ Flow นี้ใช้งานได้จริงก่อน

```text
Teacher Register/Login
       ↓
Create Classroom
       ↓
Create Design Thinking Project
       ↓
Complete 5 Steps
       ↓
AI Generate Game Schema
       ↓
Edit Game
       ↓
Publish
       ↓
Assign to Classroom
       ↓
Student Login
       ↓
Play Game
       ↓
Score Recorded
       ↓
Teacher Analytics
```

Game Components MVP:

- Scene
- Text
- Image
- Character
- Dialogue
- Multiple Choice
- True/False
- Drag & Drop
- Matching
- Collect Item
- Mission
- Score
- Timer

---

# 44. Non-Goals for MVP

ยังไม่ต้องทำ

- Multiplayer Real-time
- 3D Game Engine เต็มรูปแบบ
- VR
- AR
- Complex Physics
- User-written JavaScript
- Marketplace
- Native Mobile App
- Real-time Collaborative Game Editing

Architecture สามารถรองรับการเพิ่มภายหลังได้

---

# 45. Product Principle

ระบบต้องยึดหลัก

**Design Thinking First, AI Assisted, Teacher Controlled**

AI มีหน้าที่ช่วยสร้างและเสนอ

Teacher เป็นผู้ตัดสินใจ

Game Engine เป็นผู้ควบคุม Execution

Student เป็นผู้เล่น

Analytics เป็นข้อมูลสำหรับการปรับปรุง

วงจรหลักของ Platform คือ

```text
UNDERSTAND
   ↓
DEFINE
   ↓
IDEATE
   ↓
PROTOTYPE
   ↓
TEST
   ↓
GENERATE
   ↓
PLAY
   ↓
MEASURE
   ↓
IMPROVE
   └────────→ ITERATE
```

เป้าหมายสูงสุดคือทำให้ครูสามารถเปลี่ยนแนวคิดการเรียนรู้ให้กลายเป็น Web Game ที่ใช้งานจริงกับนักเรียนได้ โดยไม่จำเป็นต้องเขียนโปรแกรม