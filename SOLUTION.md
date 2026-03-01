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

### overview

My task list for this project was first planning it out mentally, then starting with what I can see / what will be 
displayed. I created the controller first, making a template out and testing to see if it responded correctly. Then 
moved onto the new service, I created a basic outline of this as well and connected it up to the controller requiring
minor changes to the services.yaml file. Then I created the new repositories for each of the entities I would be
Interacting with. I then spent time connecting them up into the services and controller. I then spent some time debugging
realising my implementation for the if item exists already was incorrect so spent time fixing this. Then lastly
added the entity management.

I then spent some additional time bugfixing because I wrote "likert" as "Linkert".

Overall my strategy was to work from what i could see on postman 
(at the moment my ability to debug is limited by my setup)

### explanations for choices

repositories for each entity: this is I believe the better option over continuing the original repository because it is
more readable for humans in addition for allowing symfony to utilise it's caching capabilities.

some repositories in controllers others in service layer: The way I was taught controllers should be kept as simple
as possible only doing basic checks (if item exists), as to reduce the clutter when codebases get larger. Therfore,
I have only kept simple logic checks for if the question / instance exists.

returning the object: In previous systems I have worked on we send back data about the item we have created / updated.
So, I have added the code in for now but not sending it up because it is not in the requirements


### Test result

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

created the update endpoint to allow users to change their answers, but they must keep the answer to the same question
they previously answered. Some of the data that was required before is no longer required, for my tests locally I
changed the 4 points the user gave previously to 5 below is the test responses.

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

## Resources used

 - [lucid chart](https://lucid.app/lucidchart/91bdae77-6576-4076-84f1-56741071f269/edit?beaconFlowId=6ACFC8B2839F36AA&invitationId=inv_581de3b8-b586-4c44-99f2-c6885d20bc34&page=0_0#)
 - Postman
 - chat GPT (used mostly for the Assessment sql as on my current system I don't have everything setup)