# SOLUTIONS

## Phase 1: ERD Diagram
![img.png](img.png)

The entities are:

assessment (Primary Key: id) <br>
assessment_questions (Primary Key: id) <br>
assessment_answer_options (Primary Key: id, Foreign Keys: [assessment_quesion_id]) <br>
assessment_session (Primary Key: id, Foreign Keys: [assessment_id]) <br>
assessment_instance (Primary Key: id, Foreign Keys: [session_id]) <br>
assessment_answers (Primary Key: id, Foreign Keys: [assessment_instance_id, assessment_answer_option_id]) <br>

They have the following relationships:

assessment (N : M) assessment_questions <br>
assessment (1 : N) assessment_session <br>
assessment_questions (1 : N) assessment_answer_options <br>
assessment_answer_option (1 : N) assessment_answers <br>
assessment_session (1 : N) assessment_instance <br>
assessment_instance (1 : N) assessment_answers <br>

assessment and assessment_questions have a many-to-many relationship by using the assessments_questions table to
keep the data correctly structured.

assessment_answer_options and assessment_answers have and optional 1 : N relationship meaning an assessment answer
can exist without a connected assessment_answer_option. 

### Overall Structure

we have assessments which have questions (these questions can be on multiple assessments), the questions have options
that are connected with different values given for each one, someone a part of a session can answer the question with
an answer which is then stored against there session / instance.

## Phase 2: Understand Scoring

### Testing process

using postman I sent a get request to http://localhost:8002/api/assessment/results/d1111111-1111-1111-1111-111111111111
which responded with the following response.

```
{
    "instance": {
        "id": "d1111111-1111-1111-1111-111111111111",
        "created_at": "2026-02-28 15:18:03",
        "updated_at": "2026-02-28 15:18:03",
        "completed": false,
        "completed_at": null,
        "responder_name": "Test Teacher",
        "element": "1.1"
    },
    "total_questions": 4,
    "answered_questions": 2,
    "completion_percentage": 50,
    "scores": {
        "element": "1.1",
        "total_score": 9,
        "max_score": 15,
        "percentage": 53.85
    },
    "element_scores": {
        "1.1": {
            "element": "1.1",
            "total_questions": 4,
            "answered_questions": 2,
            "completion_percentage": 50,
            "scores": {
                "total_score": 9,
                "max_score": 15,
                "percentage": 53.85
            },
            "question_answers": [
                {
                    "question_id": "a1111111-1111-1111-1111-111111111111",
                    "question_title": "How confident are you in planning engaging lessons?",
                    "question_suite": null,
                    "question_sequence": 1,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e1111111-1111-1111-1111-111111111111",
                    "answer_value": 4,
                    "answer_text": "Very confident",
                    "answer_option_id": "b1111111-1111-1111-1111-111111111114",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 4
                }, ...
            ]
        }
    },
    "insights": [
        {
            "type": "completion",
            "message": "You have 2 questions remaining to complete this assessment.",
            "positive": false
        },
        {
            "type": "performance",
            "message": "You demonstrate strong confidence in this element of teaching practice.",
            "positive": true
        }
    ]
}
```

The percentage responded back with the expected 53.85%. However, at first this seemed weird to me the user has so far
scored 9/15 points available. Therefore, if they score 0 points for there final question they should still get 60% marks.
So I looked at the calculation again <code> (total_score - answered) / (max_score - answered) * 100 </code> and realised
what answered was in for. For a given question the minimum value a user can get is 1. So even if the test taker gets
every question wrong the minimum they can get is 20%, so to "normalize" this we deduct 1 for each question the user has
answered.

The normalisation algorithm accounts for the minimum baseline a user will get just for answering the question (1 mark)
giving a more accurate representation of how they did.


### Bugs found

Looking through the code (and the hint given in the task readme) I noticed the current implementation does not take into
account if the question is a reflection and does not score the user any points. The system adds to the answered variable
even if the question does not give points. <p>

To solve this I added <code>$elementAnsweredQuestionsWithPoints</code> this keeps track of the number of answered
questions the user has given that scored points. (I used points over is reflection as I believe this is more future-proof
if functionality changes and reflections can score points then I won't need to change the code.) I used this value 
instead of <code>elementAnsweredQuestions</code> so I could keep track of number of questions answered for the completion
percentage and keep an accurate normalised percentage.


## Phase 3: Answers endpoint

### Implementation approach

I chose to go for an outward in approach, starting with the controller and moving towards the database testing my additions
as I add to the codebase. Therefore,the tests I did are relative to the point of the code I was implementing at the time.

### Test cases descriptions (Overview)
1. Instance and Question are given
2. Instance not given
3. Question not given
4. Instance does not exist in database
5. Question does not exist in database
6. when Question Type is 'likert' and valid option id is given
7. when Question Type is 'likert' option ID not given
8. when Question Type is 'likert' option id is not valid
9. when Question Type is not 'likert' and option id is given
10. question is a reflection and text is given
11. question is a reflection and text not given
12. instance given does exist for a given question
13. instance does not exist for a given question
14. item persists in database

### Edge case tests:

2,3,4,5,7,8,9,11,13

#### 1: Instance and Question are given

##### Data sent
```
{
  "instance_id": "d1111111-1111-1111-1111-111111111111",
  "question_id": "a3333333-3333-3333-3333-333333333333"
}
```
##### Response
(this test was done before following code checking for option id was implemented )
201 <code> Created </code>

#### 2: Instance not given

##### Data sent
```
{
  "question_id": "a3333333-3333-3333-3333-333333333333"
}
```
##### Response

400 <code> error: instance_id, question_id are required for this request </code>

#### 3: Question not given

##### Data sent
```
{
  "instance_id": "d1111111-1111-1111-1111-111111111111"
}
```
##### Response

400 <code> error: instance_id, question_id are required for this request </code>

#### 4: Instance does not exist in database

##### Data sent
```
{
    "instance_id": "d1111111-1111-3456-1111-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333"
}
```
##### Response

404 <code> error: instance given does not exist </code>

#### 5: Question does not exist in database

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a3333333-3333-3456-3333-333333333333"
}
```
##### Response

404 <code> error: question given does not exist </code>

#### 6: when Question Type is 'likert' and valid option id is given

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333",
    "answer_option_id": "b3333333-3333-3333-3333-333333333333"
}
```
##### Response

201 <code> Created </code>

#### 7: when Question Type is 'likert' option ID not given

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333"
}
```
##### Response

400 <code> error: answer option must be given when question type is "likert" </code>

#### 8: when Question Type is 'likert' option id is not valid

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333",
    "answer_option_id": "b3333333-3333-3333-3456-333333333333"
}
```
##### Response

404 <code> error: answer option is not found </code>

#### 9: when Question Type is not 'likert' and option id is given

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a4444444-4444-4444-4444-444444444444",
    "answer_option_id": "b3333333-3333-3333-3456-333333333333"
}
```
##### Response

400 <code> error: question type is not "likert" so option id should not be given </code>

#### 10: question is a reflection and text is given

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a4444444-4444-4444-4444-444444444444",
    "text_answer": "Text
}
```
##### Response

201 <code> Created </code>

#### 11: question is a reflection and text not given

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a4444444-4444-4444-4444-444444444444",
}
```
##### Response

400 <code> error: when question is type "reflection" text answer must be given </code>

#### 12: instance does belong to question

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333",
    "answer_option_id": "b3333333-3333-3333-3333-333333333333"
}
```
##### Response

201 <code> Created </code>

#### 13: instance does not belong to question
(this required me adding the instance to the database to test the functionality)
##### Data sent
```
{
    "instance_id": "d1111111-4567-4567-4567-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333",
    "answer_option_id": "b3333333-3333-3333-3333-333333333333"
}
```
##### Response

400 <code> error: you cannot create an answer for a question that is not included on the assessment </code>

#### 14: item persists in database

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333",
    "answer_option_id": "b3333333-3333-3333-3333-333333333333"
}
```
##### Response

201 <code> Created </code>

I also tested it remained by sending a POST REQUEST to http://localhost:8002/api/assessment/results/d1111111-1111-1111-1111-111111111111
```
{
    "instance": {
        "id": "d1111111-1111-1111-1111-111111111111",
        "created_at": "2026-02-28 15:18:03",
        "updated_at": "2026-02-28 15:18:03",
        "completed": false,
        "completed_at": null,
        "responder_name": "Test Teacher",
        "element": "1.1"
    },
    "total_questions": 4,
    "answered_questions": 3,
    "completion_percentage": 75,
    "scores": {
        "element": "1.1",
        "total_score": 12,
        "max_score": 15,
        "percentage": 75
    },
    "element_scores": {
        "1.1": {
            "element": "1.1",
            "total_questions": 4,
            "answered_questions": 3,
            "completion_percentage": 75,
            "scores": {
                "total_score": 12,
                "max_score": 15,
                "percentage": 75
            },
            "question_answers": [
                {
                    "question_id": "a1111111-1111-1111-1111-111111111111",
                    "question_title": "How confident are you in planning engaging lessons?",
                    "question_suite": null,
                    "question_sequence": 1,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e1111111-1111-1111-1111-111111111111",
                    "answer_value": 4,
                    "answer_text": "Very confident",
                    "answer_option_id": "b1111111-1111-1111-1111-111111111114",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 4
                },
                {
                    "question_id": "a2222222-2222-2222-2222-222222222222",
                    "question_title": "How often do you use formative assessment strategies?",
                    "question_suite": null,
                    "question_sequence": 2,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e2222222-2222-2222-2222-222222222222",
                    "answer_value": 5,
                    "answer_text": "Always",
                    "answer_option_id": "b2222222-2222-2222-2222-222222222225",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 5
                },
                {
                    "question_id": "a3333333-3333-3333-3333-333333333333",
                    "question_title": "To what extent do you differentiate instruction for diverse learners?",
                    "question_suite": null,
                    "question_sequence": 3,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "c60e6848-dfc7-4a42-8f68-a4a49d5244a1",
                    "answer_value": 3,
                    "answer_text": "To some extent",
                    "answer_option_id": "b3333333-3333-3333-3333-333333333333",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 3
                },
                {
                    "question_id": "a4444444-4444-4444-4444-444444444444",
                    "question_title": "Reflection",
                    "question_suite": null,
                    "question_sequence": 4,
                    "is_reflection": true,
                    "reflection_prompt": "What is one area you would like to develop further in your teaching practice?",
                    "element": "1.1",
                    "max_score": 0,
                    "is_answered": false,
                    "answer_id": null,
                    "answer_value": null,
                    "answer_text": null,
                    "answer_option_id": null,
                    "text_answer": null,
                    "numeric_value": null
                }
            ]
        }
    },
    "insights": [
        {
            "type": "completion",
            "message": "You have 1 questions remaining to complete this assessment.",
            "positive": false
        },
        {
            "type": "performance",
            "message": "You demonstrate strong confidence in this element of teaching practice.",
            "positive": true
        }
    ]
}
```

## Phase 4: Additional work

### Duplication Check
Added duplication check into the AssessmentAnswerService 

#### check for duplicate gives error

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a3333333-3333-3333-3333-333333333333",
    "answer_option_id": "b3333333-3333-3333-3333-333333333333"
}
```
##### Response

404 <code> error: this answer already exists </code>

### PUT Endpoint

For my tests locally I changed the 4 points the user gave previously to 5 below is the test responses.

##### Data sent
```
{
    "instance_id": "d1111111-1111-1111-1111-111111111111",
    "question_id": "a2222222-2222-2222-2222-222222222222",
    "answer_option_id": "b2222222-2222-2222-2222-222222222225"
}
```
##### Response

400 <code> Updated </code>

I checked to see if it persisted and below is shows the percentage has been updated

```
{
    "instance": {
        "id": "d1111111-1111-1111-1111-111111111111",
        "created_at": "2026-02-28 15:18:03",
        "updated_at": "2026-02-28 15:18:03",
        "completed": false,
        "completed_at": null,
        "responder_name": "Test Teacher",
        "element": "1.1"
    },
    "total_questions": 4,
    "answered_questions": 3,
    "completion_percentage": 75,
    "scores": {
        "element": "1.1",
        "total_score": 13,
        "max_score": 15,
        "percentage": 83.33
    },
    "element_scores": {
        "1.1": {
            "element": "1.1",
            "total_questions": 4,
            "answered_questions": 3,
            "completion_percentage": 75,
            "scores": {
                "total_score": 13,
                "max_score": 15,
                "percentage": 83.33
            },
            "question_answers": [
                {
                    "question_id": "a1111111-1111-1111-1111-111111111111",
                    "question_title": "How confident are you in planning engaging lessons?",
                    "question_suite": null,
                    "question_sequence": 1,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e1111111-1111-1111-1111-111111111111",
                    "answer_value": 5,
                    "answer_text": "Extremely confident",
                    "answer_option_id": "b1111111-1111-1111-1111-111111111115",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 5
                },
                {
                    "question_id": "a2222222-2222-2222-2222-222222222222",
                    "question_title": "How often do you use formative assessment strategies?",
                    "question_suite": null,
                    "question_sequence": 2,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e2222222-2222-2222-2222-222222222222",
                    "answer_value": 5,
                    "answer_text": "Always",
                    "answer_option_id": "b2222222-2222-2222-2222-222222222225",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 5
                },
                {
                    "question_id": "a3333333-3333-3333-3333-333333333333",
                    "question_title": "To what extent do you differentiate instruction for diverse learners?",
                    "question_suite": null,
                    "question_sequence": 3,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "c60e6848-dfc7-4a42-8f68-a4a49d5244a1",
                    "answer_value": 3,
                    "answer_text": "To some extent",
                    "answer_option_id": "b3333333-3333-3333-3333-333333333333",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 3
                },
                {
                    "question_id": "a4444444-4444-4444-4444-444444444444",
                    "question_title": "Reflection",
                    "question_suite": null,
                    "question_sequence": 4,
                    "is_reflection": true,
                    "reflection_prompt": "What is one area you would like to develop further in your teaching practice?",
                    "element": "1.1",
                    "max_score": 0,
                    "is_answered": false,
                    "answer_id": null,
                    "answer_value": null,
                    "answer_text": null,
                    "answer_option_id": null,
                    "text_answer": null,
                    "numeric_value": null
                }
            ]
        }
    },
    "insights": [
        {
            "type": "completion",
            "message": "You have 1 questions remaining to complete this assessment.",
            "positive": false
        },
        {
            "type": "performance",
            "message": "You demonstrate strong confidence in this element of teaching practice.",
            "positive": true
        }
    ]
}
```

# Resources used

 - [lucid chart](https://lucid.app/lucidchart/91bdae77-6576-4076-84f1-56741071f269/edit?beaconFlowId=6ACFC8B2839F36AA&invitationId=inv_581de3b8-b586-4c44-99f2-c6885d20bc34&page=0_0#)
 - Postman 
 - ChatGPT (not integrated with ide)