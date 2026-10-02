# Design Thinking Game Studio — UI/UX Design Specification

## 1. Design Direction

ระบบต้องมีภาพลักษณ์

- Modern
- Creative
- Educational
- Friendly
- Professional
- Clean
- AI-assisted
- Game-inspired

หลีกเลี่ยง UI ที่ดูเหมือนระบบราชการหรือ LMS แบบเก่า

Interface ต้องให้ความรู้สึกใกล้เคียงกับ

**Modern SaaS + Creative Tool + Game Builder**

แต่ยังใช้งานง่ายสำหรับครูที่ไม่ได้มีพื้นฐานด้านเทคนิค

---

# 2. Design Principle

ใช้หลัก

```text
Simple
Visual
Guided
Creative
Consistent
Accessible
```

ทุกหน้าควรตอบได้ทันทีว่า

1. ตอนนี้ผู้ใช้อยู่ตรงไหน
2. กำลังทำอะไร
3. ขั้นตอนต่อไปคืออะไร

---

# 3. UI Framework

ใช้

```text
Vue 3
Vuetify 3
Material Design 3
```

แต่ไม่ควรใช้ Default Vuetify Appearance ทั้งหมด

ต้อง Customize

- Border Radius
- Typography
- Spacing
- Navigation
- Cards
- Buttons
- Colors

ให้มี Product Identity ของตัวเอง

---

# 4. Color System

Primary:

```text
#6366F1
```

Indigo ใช้กับ

- Primary Button
- Navigation Active
- Main Actions
- Links

Secondary:

```text
#8B5CF6
```

ใช้กับ

- AI
- Creative Features
- Gradient
- Game Builder

Accent:

```text
#06B6D4
```

ใช้กับ

- Progress
- Interactive Elements
- Analytics

Success:

```text
#22C55E
```

Warning:

```text
#F59E0B
```

Error:

```text
#EF4444
```

Background:

```text
#F8FAFC
```

Surface:

```text
#FFFFFF
```

Main Text:

```text
#0F172A
```

Secondary Text:

```text
#64748B
```

Border:

```text
#E2E8F0
```

---

# 5. AI Gradient

AI Feature สามารถใช้ Gradient

```text
#6366F1 → #8B5CF6
```

ใช้เฉพาะ

- Generate Game
- AI Assistant
- AI Suggestion
- AI Asset Generation

อย่าใช้ Gradient มากเกินไป

---

# 6. Typography

ภาษาไทยแนะนำ

```text
Anuphan
```

Fallback:

```text
Anuphan,
Noto Sans Thai,
sans-serif
```

Font Weight:

```text
400 Regular
500 Medium
600 SemiBold
700 Bold
```

---

# 7. Border Radius

ใช้ Rounded UI

```text
Small      8px
Default   12px
Card      16px
Large     20px
Dialog    20px
```

หลีกเลี่ยง Card เหลี่ยมแข็ง

---

# 8. Shadow

ใช้ Shadow เบา

Card ปกติใช้ Border เป็นหลัก

```text
border: 1px solid #E2E8F0
```

Shadow ใช้เฉพาะ

- Floating Toolbar
- Dialog
- Dropdown
- Selected Game Object
- Dragging Object

---

# 9. Application Layout

Desktop:

```text
+-------------------------------------------------------+
| Top Bar                                               |
+------------+------------------------------------------+
|            |                                          |
| Sidebar    |               Content                    |
|            |                                          |
|            |                                          |
|            |                                          |
+------------+------------------------------------------+
```

Sidebar:

```text
260px expanded
72px collapsed
```

---

# 10. Main Navigation

Teacher Navigation:

```text
Dashboard

WORKSPACE
Design Projects
My Games
Asset Library

CLASSROOM
My Classes
Students
Assignments

INSIGHTS
Analytics

SYSTEM
AI Settings
Profile
```

ใช้ Icons คู่กับ Label

---

# 11. Top Bar

ประกอบด้วย

```text
Breadcrumb

                    Search

             Notifications

             User Avatar
```

ไม่ควรสูงเกินไป

ประมาณ

```text
64px
```

---

# 12. Dashboard

Dashboard ต้องไม่เต็มไปด้วยตัวเลข

ส่วนบน:

```text
สวัสดี, คุณครู 👋

วันนี้อยากสร้างประสบการณ์การเรียนรู้อะไร?

[ + สร้างโปรเจกต์ใหม่ ]    [ ✨ สร้างเกมด้วย AI ]
```

Statistics:

```text
+---------------+ +---------------+
| 🎮 Games      | | 👨‍🎓 Students  |
|     12        | |      128      |
+---------------+ +---------------+

+---------------+ +---------------+
| 🏫 Classes    | | ▶ Plays       |
|      5        | |     1,284     |
+---------------+ +---------------+
```

ด้านล่าง

```text
Recent Projects

Recent Games

Student Activity
```

---

# 13. Design Thinking Workspace

นี่คือหน้าที่สำคัญที่สุดหน้าหนึ่ง

Layout:

```text
+-------------------------------------------------------+
| Project Name                              Saved ✓      |
+-------------------------------------------------------+

  ✓ Empathize
        |
  ✓ Define
        |
  ● Ideate
        |
  ○ Prototype
        |
  ○ Test

+-------------------------------------------------------+
|                                                       |
|                 Current Step Form                     |
|                                                       |
+-------------------------------------------------------+

                         [Back] [Save & Continue →]
```

Desktop สามารถใช้ Step Navigation ด้านซ้าย

Mobile ใช้ Horizontal Stepper

---

# 14. Step Identity

แต่ละขั้นมี Visual Identity เล็กน้อย

Empathize

```text
Icon: people / heart
Concept: Understand
```

Define

```text
Icon: target
Concept: Focus
```

Ideate

```text
Icon: lightbulb
Concept: Imagine
```

Prototype

```text
Icon: construction / palette
Concept: Create
```

Test

```text
Icon: experiment / play
Concept: Validate
```

อย่าเปลี่ยน Theme ทั้งหน้าในแต่ละ Step

ใช้เพียง Icon และ Accent

---

# 15. Form Design

หลีกเลี่ยง Form ยาวแบบ

```text
Label
Input
Label
Input
Label
Input
```

ให้แบ่งเป็น Section Card

ตัวอย่าง

```text
┌────────────────────────────────────┐
│ 👨‍🎓 ผู้เรียนของคุณ                 │
│                                    │
│ ระดับชั้น       อายุ               │
│ [______]       [______]            │
│                                    │
│ วิชา                               │
│ [____________________________]     │
└────────────────────────────────────┘
```

---

# 16. AI Assistant

ทุก Design Thinking Step สามารถมี AI Assistant

อย่าใช้ Chatbot Popup เป็น Interface หลัก

ใช้ Contextual Assistant

ตัวอย่าง:

```text
┌───────────────────────────────────┐
│ ✨ AI Suggestion                  │
│                                   │
│ จากข้อมูลผู้เรียน AI พบว่า...      │
│                                   │
│ • นักเรียนชอบการแข่งขัน            │
│ • ระยะเวลาเรียนค่อนข้างสั้น         │
│                                   │
│ [Use Suggestion] [Try Again]      │
└───────────────────────────────────┘
```

Teacher ต้องเป็นผู้เลือกว่าจะใช้หรือไม่

---

# 17. Generate Game Screen

หลังทำ Design Thinking เสร็จ

แสดง Summary ก่อน

```text
Your Learning Game

Target
ม.1

Subject
วิทยาศาสตร์

Problem
นักเรียนไม่เข้าใจการแยกขยะ

Learning Goal
จำแนกประเภทขยะได้ถูกต้อง

Game Concept
School Recycling Adventure
```

CTA:

```text
✨ Generate Learning Game
```

---

# 18. AI Generation UI

เมื่อ Generate ห้ามใช้ Spinner อย่างเดียว

แสดง Progress

```text
✨ กำลังสร้างเกมของคุณ

✓ วิเคราะห์ผู้เรียน
✓ วิเคราะห์ Learning Objective
✓ สร้าง Game Concept
● กำลังออกแบบ Missions
○ กำลังเลือก Assets
○ กำลังสร้าง Game
○ ตรวจสอบ Game

████████████░░░░░ 68%
```

ทำให้ผู้ใช้เข้าใจว่าระบบกำลังทำอะไร

---

# 19. Game Builder

Game Builder ใช้ Layout แบบ Creative Tool

```text
+--------------------------------------------------------+
| Back | Game Name | Saved ✓ | Preview | Publish         |
+---------+------------------------------+---------------+
|         |                              |               |
| TOOL    |                              | PROPERTIES    |
|         |                              |               |
| Select  |                              | Position      |
| Text    |         GAME CANVAS          | Size          |
| Image   |                              | Animation     |
| Actor   |                              | Action        |
| Object  |                              | Score         |
| Quiz    |                              |               |
|         |                              |               |
+---------+------------------------------+---------------+
| Scene 1 | Scene 2 | Scene 3 | + Scene                  |
+--------------------------------------------------------+
```

---

# 20. Game Builder Canvas

Canvas ควรมี Aspect Ratio Preset

```text
16:9 Desktop
4:3 Tablet
9:16 Mobile
```

Default:

```text
16:9
```

แสดง Safe Area

---

# 21. Builder Toolbar

Tool Categories:

```text
Basic
├ Text
├ Image
├ Button

Game
├ Character
├ Object
├ Dialogue
├ Mission
├ Timer

Learning
├ Multiple Choice
├ True / False
├ Matching
├ Sorting
├ Drag & Drop

Assets
└ Asset Library
```

---

# 22. Property Inspector

เมื่อเลือก Object

แสดงเฉพาะ Property ที่เกี่ยวข้อง

ตัวอย่าง Character:

```text
Character

Name
[ Student ]

Position
X [120] Y [320]

Size
W [180] H [260]

Animation
[ Idle ▼ ]

On Click
[ Show Dialogue ▼ ]
```

---

# 23. Asset Library UI

Layout:

```text
+------------------------------------------------+
| Asset Library                                  |
|                                                |
| 🔍 Search assets...                            |
|                                                |
| [All] [Character] [Object] [Background] [UI] |
|                                                |
| Style: [All ▼] Theme: [School ▼]              |
|                                                |
| ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐              |
| │     │ │     │ │     │ │     │              |
| │ 👦  │ │ 👧  │ │ 🏫  │ │ 🗑️ │              |
| │     │ │     │ │     │ │     │              |
| └─────┘ └─────┘ └─────┘ └─────┘              |
+------------------------------------------------+
```

---

# 24. Asset Card

Card เน้น Preview

เมื่อ Hover:

```text
Preview
Name
Type

[Use]
```

รายละเอียด License ไม่จำเป็นต้องแสดงตลอดเวลา

แต่ต้องดูได้จาก Asset Detail

---

# 25. AI Asset Search

Search รองรับ Natural Language

เช่น

```text
นักเรียนชายกำลังถือหนังสือ
```

หรือ

```text
school classroom background
```

UI สามารถแสดง

```text
✨ AI Search
```

เพื่อแยกจาก Keyword Search

---

# 26. Classroom

Classroom Card:

```text
┌──────────────────────────┐
│ วิทยาศาสตร์ ม.1/2        │
│                          │
│ 👨‍🎓 32 Students          │
│ 🎮 4 Games               │
│                          │
│ Join Code                │
│ DT-X92KD                 │
│                          │
│ [Open Classroom]         │
└──────────────────────────┘
```

---

# 27. Assignment UI

แสดง Game Thumbnail

```text
┌────────────────────────────────────┐
│ [ GAME IMAGE ]                     │
│                                    │
│ ภารกิจโรงเรียนไร้ขยะ                │
│                                    │
│ Due: 20 Aug                        │
│ Attempts: 2/3                      │
│                                    │
│ Score: 85                          │
│                                    │
│ [Play Game]                        │
└────────────────────────────────────┘
```

---

# 28. Student UI

Student UI ต้องง่ายกว่า Teacher มาก

Navigation:

```text
Home
My Classes
My Games
My Results
```

ไม่แสดงระบบซับซ้อน

---

# 29. Student Home

Hero:

```text
สวัสดี ปัญญา 👋

พร้อมสำหรับภารกิจวันนี้หรือยัง?
```

ด้านล่าง

```text
Continue Playing

New Missions

Completed
```

ใช้ Game Thumbnail ขนาดใหญ่

---

# 30. Game Player

Game Player ใช้ Full Screen

```text
+------------------------------------------------+
| Mission             Score 40        ⚙           |
+------------------------------------------------+
|                                                |
|                                                |
|                   GAME                         |
|                                                |
|                                                |
+------------------------------------------------+
| Progress ███████████░░░                       |
+------------------------------------------------+
```

ลด Navigation ที่ไม่เกี่ยวข้องออก

---

# 31. Feedback

Correct:

```text
✓ ถูกต้อง!

+10 คะแนน
```

Incorrect:

```text
ลองอีกครั้ง

คำใบ้:
ขวดพลาสติกสามารถนำกลับมาใช้ใหม่ได้
```

Feedback ควรให้ความรู้ ไม่ใช่แค่บอกผิด

---

# 32. Results Screen

```text
🎉 ภารกิจสำเร็จ!

       85
      /100

⭐⭐⭐

เวลา
08:42

ตอบถูก
8 / 10

ภารกิจ
5 / 5

[Play Again]

[Back to Classroom]
```

---

# 33. Analytics Dashboard

Teacher Analytics:

```text
Game Performance

Average Score
78

Completion
84%

Average Time
12:32

Attempts
1.8
```

Chart ใช้เฉพาะเมื่อช่วยให้เข้าใจข้อมูล

ไม่สร้าง Dashboard ที่เต็มไปด้วย Graph

---

# 34. Difficult Areas

ควรมี Section

```text
Needs Attention

Question 7
Correct: 32%

Mission 3
Completion: 48%

Scene 4
Exit rate: 37%
```

เพื่อให้ Teacher นำข้อมูลไปปรับเกมได้ทันที

---

# 35. AI Improvement

แสดงเป็น Recommendation

```text
✨ AI Improvement

นักเรียน 68% ตอบคำถามข้อ 7 ผิด

AI แนะนำ:

ลดความซับซ้อนของคำถาม
และเพิ่มตัวอย่างก่อนเข้าสู่คำถาม

[View Suggestion]

[Apply to New Version]
```

ห้าม Apply อัตโนมัติ

---

# 36. Empty States

ทุก Empty State ต้องช่วยผู้ใช้ไปต่อ

ไม่ใช้แค่

```text
No data
```

ใช้

```text
ยังไม่มีเกม

เริ่มสร้างเกมการเรียนรู้เกมแรกของคุณ

[+ Create Game]
```

---

# 37. Loading

ใช้ Skeleton สำหรับ

- Dashboard
- Game List
- Asset Library
- Analytics

ใช้ Progress UI สำหรับ

- AI Generation
- Asset Import
- Publishing

---

# 38. Dialog

ใช้ Dialog เฉพาะ Action สั้น

เช่น

- Delete
- Rename
- Duplicate
- Assign

งานที่มีหลายขั้นตอนควรใช้ Page หรือ Drawer

---

# 39. Notifications

ใช้ Snackbar สำหรับ

```text
Saved successfully
Game published
Asset uploaded
```

Error ต้องอธิบายได้

ไม่ใช้

```text
Error 500
```

กับผู้ใช้ทั่วไป

---

# 40. Responsive

Breakpoints:

```text
Mobile
< 600

Tablet
600 - 959

Desktop
960+
```

Teacher Game Builder สามารถแสดงข้อความแนะนำให้ใช้ Desktop/Tablet หากหน้าจอเล็กเกินไป

Student Game Player ต้อง Mobile Friendly เต็มรูปแบบ

---

# 41. Accessibility

ต้องรองรับ

- Keyboard Navigation
- Visible Focus
- ARIA Label
- Contrast
- Alt Text
- Large Touch Target

Touch Target อย่างน้อยประมาณ

```text
44 x 44px
```

อย่าใช้สีอย่างเดียวในการบอกสถานะ

---

# 42. Iconography

ใช้ Material Design Icons ที่มาพร้อม Vuetify

ไม่ผสมหลาย Icon Library โดยไม่จำเป็น

Icon ต้องมี Style เดียวกันทั้งระบบ

---

# 43. Animation

ใช้ Motion เล็กน้อย

เช่น

- Page Transition
- Card Hover
- Drag & Drop
- Score Increase
- Game Feedback
- AI Generating

Animation ประมาณ

```text
150-300ms
```

หลีกเลี่ยง Animation ที่รบกวนการใช้งาน

---

# 44. Game Visual Style

เกมสามารถมี Style แตกต่างกันได้

เช่น

```text
Cartoon
Flat Education
Pixel
Fantasy
Science
Space
Nature
School
```

แต่ UI ของ Platform ต้องคง Design System เดิม

Game Theme และ Platform Theme เป็นคนละระบบ

---

# 45. Important UX Rule

อย่าทำให้ Teacher รู้สึกว่ากำลังเขียนโปรแกรม

หลีกเลี่ยงคำเช่น

```text
JSON
Schema
Variable
Event Handler
Boolean
Payload
```

ใน Teacher UI

ใช้คำที่เข้าใจง่าย เช่น

```text
ฉาก
ตัวละคร
วัตถุ
เมื่อคลิก
ไปยังฉาก
เพิ่มคะแนน
คำถาม
ภารกิจ
```

Technical terminology สามารถใช้เฉพาะ Admin/Developer Mode

---

# 46. AI UX Principle

AI ไม่ควรเป็นสิ่งที่บังคับใช้

ทุก Feature หลักควรมี

```text
Manual
+
AI Assisted
```

เช่น

```text
สร้าง Game Concept เอง

หรือ

✨ ให้ AI ช่วยคิด
```

Teacher ต้องสามารถแก้ไขผลลัพธ์ AI ได้ทุกครั้ง

---

# 47. Core Product Experience

ประสบการณ์หลักของระบบต้องให้ความรู้สึกว่า

```text
เข้าใจผู้เรียน
      ↓
กำหนดปัญหา
      ↓
คิดเกม
      ↓
สร้างต้นแบบ
      ↓
ทดลอง
      ↓
✨ AI ช่วยสร้างเกม
      ↓
🎮 นักเรียนเล่น
      ↓
📊 เห็นผลการเรียนรู้
      ↓
💡 ปรับปรุง
```

ไม่ควรทำให้ Design Thinking เป็นเพียง Form 5 หน้า

แต่ต้องทำให้ผู้ใช้รู้สึกว่าแต่ละขั้นตอนส่งผลต่อเกมที่กำลังถูกสร้างขึ้น

---

# 48. Final Design Goal

UI ต้องทำให้ Teacher สามารถเริ่มจาก

> "ฉันมีปัญหาในการสอนเรื่องนี้"

และจบที่

> "ฉันมีเกมที่นักเรียนสามารถเล่นได้จริง"

โดยไม่ต้องเขียน Code

Product Experience จึงต้องอยู่ตรงกลางระหว่าง

**Design Thinking Tool + Canva-like Editor + AI Game Generator + Classroom Platform**

และทุกการตัดสินใจด้าน UI/UX ต้องสนับสนุน Workflow นี้เป็นหลัก