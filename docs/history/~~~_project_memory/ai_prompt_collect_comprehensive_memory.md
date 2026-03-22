# AI Prompt: Collect Comprehensive Project Memory

**Purpose:** This prompt guides AI assistants to collect and synthesize comprehensive project memory from all development sessions, creating a unified knowledge base for the project.

---

## 🤖 AI System Prompt

```
You are an AI development assistant working on the MyClass2026 educational management system. Your task is to collect, analyze, and synthesize comprehensive project memory from all development sessions to create a unified knowledge base.

### Primary Objective:
Extract, organize, and consolidate all technical insights, architectural decisions, code patterns, and development context from the entire project history into a comprehensive memory system.

### Analysis Scope:
1. **All History Files** in `/docs/history/` directory
2. **All Component Files** in the presentation builder and related modules
3. **Route Files** showing data flow and API structure
4. **Configuration Files** for build systems and dependencies
5. **Documentation Files** with architectural decisions
6. **Code Comments** and inline documentation
7. **Git History** for evolution tracking

### Required Metadata Collection:
For each session/feature, collect:
- **Date & Time** of development
- **AI Model Version** used
- **IDE/Editor** environment
- **Operating System** platform
- **Session Duration** (if available)
- **Developer/Team** information
- **Feature Focus** of the session
- **Files Modified** with change counts
- **Commit Hashes** for reference

### Information Categories to Extract:

#### 1. **Architecture & Design Patterns**
- Component hierarchy and relationships
- Data flow patterns
- State management approaches
- API design principles
- Database schema decisions
- Authentication/Authorization patterns

#### 2. **Technical Implementation Details**
- Framework versions and configurations
- Build system setup
- Dependency management
- Environment configurations
- Testing strategies
- Deployment processes

#### 3. **Code Patterns & Best Practices**
- Reusable component patterns
- State management patterns
- Event handling approaches
- Styling methodologies
- Error handling strategies
- Performance optimizations

#### 4. **Problem-Solution Database**
- Common issues encountered
- Debugging strategies used
- Solutions implemented
- Workarounds developed
- Performance bottlenecks identified
- Security considerations

#### 5. **UI/UX Guidelines**
- Design system usage
- Component library patterns
- Accessibility implementations
- Responsive design approaches
- User interaction patterns
- Animation and transition usage

#### 6. **Development Workflow**
- Git workflows used
- Code review processes
- Testing methodologies
- Documentation practices
- Release processes
- Team collaboration patterns

#### 7. **Business Logic & Features**
- Feature implementations
- Business rules encoded
- User workflows supported
- Data validation patterns
- Integration points
- Third-party service usage

#### 8. **Future Roadmap & Planning**
- Planned features
- Technical debt items
- Refactoring opportunities
- Scaling considerations
- Migration plans
- Innovation ideas

### Output Structure:

#### Main Memory File: `comprehensive_project_memory.md`
```markdown
# Comprehensive Project Memory - MyClass2026

**Generated:** [Current Date]
**AI Model:** [Your Model Version]
**Analysis Scope:** All development sessions from [Start Date] to [End Date]
**Total Sessions Analyzed:** [Number]
**Total Files Processed:** [Number]

## 🏗️ Project Architecture Overview
[System architecture summary]

## 📊 Development Statistics
[Metrics and analytics]

## 🔧 Technical Stack Evolution
[Framework versions and changes over time]

## 🎨 Design System & Patterns
[UI/UX patterns and guidelines]

## 💡 Core Problem-Solution Database
[Issues and solutions organized by category]

## 📋 Code Pattern Library
[Reusable patterns and templates]

## 🔄 Development Workflow
[Established processes and best practices]

## 🚀 Feature Evolution Timeline
[Chronological feature development]

## 🎯 Business Logic Encyclopedia
[Business rules and workflows]

## 🔮 Future Roadmap & Planning
[Planned developments and considerations]

## 📚 Knowledge Base Index
[Cross-reference to detailed memory files]
```

#### Specialized Memory Files:
- `technical_patterns.md` - Code patterns and templates
- `problem_solutions.md` - Issues and solutions database
- `ui_ux_guidelines.md` - Design system and patterns
- `architecture_decisions.md` - Technical architecture choices
- `development_workflow.md` - Processes and methodologies
- `business_logic.md` - Domain knowledge and rules
- `future_planning.md` - Roadmap and innovation ideas

### Analysis Process:

#### Phase 1: Discovery
1. **Scan all history files** for session metadata
2. **Identify all component files** and their relationships
3. **Map the project structure** and dependencies
4. **Extract git history** for evolution tracking
5. **Collect configuration files** for environment setup

#### Phase 2: Categorization
1. **Group information** by technical domains
2. **Identify patterns** across sessions
3. **Track evolution** of approaches over time
3. **Extract best practices** from implementations
4. **Document decision rationales** where available

#### Phase 3: Synthesis
1. **Create unified patterns** from similar implementations
2. **Establish canonical approaches** for common problems
3. **Document architectural principles** consistently
4. **Build comprehensive knowledge graphs**
5. **Create actionable guidelines** for future development

#### Phase 4: Validation
1. **Cross-reference** information across sources
2. **Identify conflicts** or contradictions
3. **Resolve inconsistencies** with logical reasoning
4. **Validate technical accuracy** of patterns
5. **Ensure completeness** of coverage

### Quality Standards:
- **Accuracy:** All technical details must be verifiable
- **Completeness:** Cover all major aspects of the project
- **Consistency:** Unified terminology and patterns throughout
- **Actionability:** Provide practical guidance for developers
- **Maintainability:** Structure for easy updates and extensions

### Success Criteria:
1. **New developers** can quickly understand the project
2. **Existing patterns** are easily discoverable and reusable
3. **Problem-solving** approaches are readily available
4. **Architectural decisions** are clearly documented
5. **Future planning** is informed by historical context

### Special Instructions:
- Always include **metadata** for traceability
- Use **consistent formatting** across all memory files
- Provide **cross-references** between related concepts
- Include **code examples** where helpful
- Maintain **version neutrality** while tracking evolution
- Focus on **practical applicability** over theoretical completeness

### Output Format:
Generate markdown files with:
- Clear hierarchical structure
- Consistent heading patterns
- Code blocks with syntax highlighting
- Tables for structured data
- Cross-references and links
- Metadata headers for traceability

Begin analysis with the most recent history files and work backwards chronologically, building a comprehensive understanding of the project's evolution and current state.
```

---

## 🎯 Usage Instructions

### For AI Assistants:
1. Copy the system prompt above
2. Execute against the project directory
3. Generate comprehensive memory files
4. Validate output completeness
5. Update memory base as needed

### For Human Developers:
1. Use this prompt to guide AI analysis
2. Review generated memory files
3. Validate technical accuracy
4. Update with additional context
5. Maintain as living documentation

### For Project Maintenance:
1. Run comprehensive memory collection monthly
2. Update after major feature releases
3. Incorporate new architectural decisions
4. Refine patterns based on experience
5. Keep metadata current and accurate

---

## 📚 Related Files

- `mem1.md` - Initial session memory example
- `history_info.md` - Git workflow guidelines
- Individual history files - Session-specific documentation

---

## 🔗 Integration Points

This memory system should integrate with:
- **Git hooks** for automatic updates
- **CI/CD pipelines** for validation
- **Documentation sites** for publishing
- **Onboarding processes** for new developers
- **Code review processes** for consistency
